<?php

namespace Modules\DietaryManagment\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\DietaryManagment\Models\RestaurantCertification;
use Modules\DietaryManagment\Notifications\DietaryCertificateNotification;
use Modules\Notifications\Services\NotificationService;
use Throwable;

class DietaryCertificateNotificationService
{
    public function schedule(RestaurantCertification $certificate, string $action): void
    {
        try {
            $certificate->loadMissing(['restaurant.admin', 'certificationType.dietaryLabel']);
            $owner = $certificate->restaurant?->admin;
            if (!$owner || !isset(DietaryCertificateNotification::EVENTS[$action])) return;
            // Snapshot this event before another submission or review changes the record.
            $data = [
                'restaurant_name' => $certificate->restaurant->name, 'owner_name' => $owner->name,
                'certificate_name' => $certificate->certificationType?->name ?? 'Dietary certificate',
                'dietary_label' => $certificate->certificationType?->dietaryLabel?->name ?? '',
                'file_name' => $certificate->file_name,
                'submitted_at' => $certificate->submitted_at?->format('M d, Y h:i A T'),
                'reviewed_at' => $certificate->reviewed_at?->format('M d, Y h:i A T'),
                'admin_notes' => $certificate->admin_notes,
            ];
            $event = 'DietaryCertificate'.DietaryCertificateNotification::EVENTS[$action];
            DB::afterCommit(function () use ($owner, $event, $data) {
                try {
                    $sent = app(NotificationService::class)->trigger('DietaryManagment', $event, $owner, $data);
                    if (!$sent) $this->reportSafely(new \RuntimeException('Dietary certificate notification could not be queued: '.$event));
                } catch (Throwable $e) {
                    $this->reportSafely($e);
                }
            });
        } catch (Throwable $e) {
            $this->reportSafely($e);
        }
    }

    private function reportSafely(Throwable $e): void
    {
        // The shared handler can flash redirect errors. A notification failure must
        // not turn a successful upload/review into an error message on the web screen.
        $request = app()->bound('request') ? request() : null;
        $session = $request?->hasSession() ? $request->session() : null;
        $keys = ['error', '_old_input', '_flash'];
        $original = $session?->only($keys) ?? [];
        try {
            $this->reportException($e);
        } catch (Throwable $reportingError) {
            Log::warning('Unable to report dietary certificate notification failure.', ['exception_class' => get_class($reportingError)]);
        } finally {
            if ($session) {
                foreach ($keys as $key) {
                    if (array_key_exists($key, $original)) $session->put($key, $original[$key]);
                    else $session->forget($key);
                }
            }
        }
    }

    protected function reportException(Throwable $e): void
    {
        ApiExceptionHandler::handle($e);
    }
}
