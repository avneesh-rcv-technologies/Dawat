<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\EmailNotification\Models\NotificationTemplate as EmailTemplate;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationMapping;
use ReflectionClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\WebPushNotification\Models\NotificationTemplate as PushTemplate;
use Modules\Notifications\Contracts\Notification;
use Throwable;

/**
 * Completes email and web-push mappings after module seeders have registered templates.
 * Existing template choices, recipient flags, provider settings, and channel credentials
 * are preserved. Missing notification classes and recipients are repaired independently.
 */
class NotificationTemplateMappingSeeder extends Seeder
{
    /** Legacy templates whose names don't follow the event code. event_code => template name */
    private const ALIASES = [
        'otp_access_request' => 'Admin Access OTP',
        'error_occurred' => 'Error Notification',
        'otp_verification' => 'OTP Verification Email',
        'order_status_updated' => 'order_status_update',
    ];

    private const SUFFIXES = ['customer', 'owner', 'restaurant', 'user', 'admin', 'email', 'notification', 'template'];

    /** @var array<string, class-string>|null */
    private ?array $classesByEventCode = null;

    public function run(): void
    {
        (new NotificationChannelsSeeder())->run();
        $rows = [];
        DB::transaction(function () use (&$rows) {
            $catalogs = [
                'email' => EmailTemplate::orderBy('id')->get(),
                'webpush' => PushTemplate::orderBy('id')->get(),
            ];
            $active = [
                'email' => $catalogs['email']->where('status', true),
                'webpush' => $catalogs['webpush']->where('is_active', true),
            ];
            $byName = [];
            foreach ($active as $channel => $templates) {
                $byName[$channel] = $templates->groupBy(fn ($template) => $this->normalise($template->name))
                    ->map(fn ($matches) => $channel === 'email'
                        ? ($matches->firstWhere('locale', config('app.locale', 'en')) ?? $matches->firstWhere('locale', 'en') ?? $matches->first())
                        : $matches->first());
            }
            // Template-only seeders (such as admin access OTP) also need an event record.
            $this->classForEventCode('');
            foreach ($this->classesByEventCode as $code => $class) {
                $candidate = new NotificationEvent(['event_code' => $code, 'event_name' => Str::headline($code), 'notification_class' => $class]);
                if ($this->findTemplate($candidate, $byName['email']) || $this->findTemplate($candidate, $byName['webpush'])) {
                    NotificationEvent::firstOrCreate(['event_code' => $code], [
                        'event_name' => Str::headline($code), 'notification_class' => $class,
                        'event_description' => 'Notification template registration',
                    ]);
                }
            }
            foreach (NotificationEvent::orderBy('id')->get() as $event) {
                $notification = $this->instantiate($event->notification_class);
                try { $matches = $notification && $notification->getEventCode() === $event->event_code; } catch (Throwable) { $matches = false; }
                if (!$matches) {
                    if ($class = $this->classForEventCode($event->event_code)) {
                        $event->update(['notification_class' => $class]);
                        $notification = $this->instantiate($class);
                    }
                }
                foreach (['email', 'webpush'] as $name) {
                    $channel = NotificationChannel::where('name', $name)->firstOrFail();
                    $mapping = NotificationMapping::firstOrNew(['event_id' => $event->id, 'channel_id' => $channel->id]);
                    $template = $catalogs[$name]->firstWhere('id', (int) $mapping->template_id)
                        ?? $this->findTemplate($event, $byName[$name]);
                    if (!$template) continue; // Do not guess an unrelated template or invent a new delivery channel.
                    $mapping->template_id = (string) $template->id;
                    if (empty($mapping->recipients)) {
                        $legacy = $mapping->getRawOriginal('custom_config');
                        for ($i = 0; is_string($legacy) && $i < 2; ++$i) $legacy = json_decode($legacy, true);
                        $defaults = $this->defaultRecipients($event);
                        $legacy = is_array($legacy) ? array_intersect_key($legacy, $defaults ?? []) : [];
                        $mapping->recipients = array_replace($defaults ?? [], array_map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN), $legacy));
                    }
                    if (!$mapping->exists || $mapping->isDirty()) {
                        $mapping->save();
                        $rows[] = [$event->event_code, $name, $template->name];
                    }
                }
            }
            foreach ($catalogs['webpush'] as $template) {
                $variables = $template->variables;
                for ($i = 0; is_string($variables) && $i < 2; ++$i) $variables = json_decode($variables, true);
                if (is_array($variables) && $variables !== $template->variables) $template->update(['variables' => $variables]);
            }
        });
        if ($rows) $this->command?->table(['Event', 'Channel', 'Mapped template'], $rows);
        $this->command?->info('Email and web-push template mappings and recipients synchronized.');
    }

    private function findTemplate(NotificationEvent $event, Collection $byName): ?Model
    {
        $code = $this->normalise($event->event_code);
        $name = $this->normalise((string) $event->event_name);
        $stripped = $this->normalise(preg_replace('/\b(email|notification|template)s?\b/i', '', (string) $event->event_name));

        $candidates = [$code, $name];
        foreach (self::SUFFIXES as $suffix) {
            $candidates[] = $code.'_'.$suffix;
        }
        $candidates[] = $stripped;
        if ($class = $event->notification_class ?: $this->classForEventCode($event->event_code)) {
            $candidates[] = $this->normalise(Str::snake(class_basename($class)));
        }
        $candidates[] = $this->normalise(self::ALIASES[$event->event_code] ?? null);

        foreach (array_filter(array_unique($candidates)) as $key) {
            if ($byName->has($key)) {
                return $byName->get($key);
            }
        }

        return null;
    }

    /**
     * Enable the recipients the notification sends to directly (customer, owner, …).
     */
    private function defaultRecipients(NotificationEvent $event): ?array
    {
        $notification = $this->instantiate($event->notification_class ?: $this->classForEventCode($event->event_code));
        if (! $notification || ! method_exists($notification, 'getAvailableRecipients')) {
            return null;
        }

        try {
            $types = method_exists($notification, 'getRecipientTypeMapping') ? $notification->getRecipientTypeMapping() : [];
            $recipients = [];
            foreach (array_keys($notification->getAvailableRecipients()) as $key) {
                $recipients[$key] = ($types[$key] ?? null) === 'notifiable';
            }

            if ($recipients && !in_array(true, $recipients, true) && isset($recipients['notify_admin'])) $recipients['notify_admin'] = true;
            return $recipients ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    private function instantiate(?string $class): ?object
    {
        if (! $class || ! class_exists($class)) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($class);
            if (! $reflection->isInstantiable()) {
                return null;
            }
            if (!$reflection->implementsInterface(Notification::class)) return null;
            return $reflection->newInstanceWithoutConstructor();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Find the notification class for events seeded without notification_class,
     * by scanning each module's Notifications folder for a matching getEventCode().
     */
    private function classForEventCode(string $eventCode): ?string
    {
        if ($this->classesByEventCode === null) {
            $this->classesByEventCode = [];
            foreach (glob(base_path('Modules/*/src/Notifications/{,*/}*.php'), GLOB_BRACE) ?: [] as $file) {
                $relative = Str::after($file, base_path('Modules').DIRECTORY_SEPARATOR);
                [$module, $rest] = explode('/src/', str_replace('\\', '/', $relative), 2);
                $class = 'Modules\\'.$module.'\\'.str_replace(['/', '.php'], ['\\', ''], $rest);

                $notification = $this->instantiate($class);
                if ($notification && method_exists($notification, 'getEventCode')) {
                    try {
                        $this->classesByEventCode[(string) $notification->getEventCode()] ??= $class;
                    } catch (Throwable) {
                        // Event code depends on constructor data; skip.
                    }
                }
            }
        }

        return $this->classesByEventCode[$eventCode] ?? null;
    }

    private function normalise(?string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', Str::lower(trim((string) $value))), '_');
    }
}
