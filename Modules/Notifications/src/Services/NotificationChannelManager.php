<?php

namespace Modules\Notifications\Services;

use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Contracts\NotificationChannel as NotificationChannelContract;

class NotificationChannelManager
{
    protected array $channels = [];

    public function registerChannel(string $name, string $handlerClass, array $config = []): void
    {
        $this->channels[$name] = [
            'handler' => $handlerClass,
            'config' => $config,
            'is_active' => true
        ];

        // Sync with database
        $this->updateChannelInDatabase($name, $handlerClass, $config, true);
    }

    public function getChannel(string $name): ?array
    {
        // First check in-memory channels
        if (isset($this->channels[$name])) {
            return $this->channels[$name];
        }

        // Fallback to database
        $channel = NotificationChannel::where('name', $name)->first();

        if ($channel) {
            return [
                'handler' => $channel->channel_class,
                'config' => $channel->config ?? [],
                'is_active' => $channel->status
            ];
        }

        return null;
    }

    public function getAvailableChannels(): array
    {
        // Merge in-memory and database channels
        $dbChannels = NotificationChannel::where('status', true)
            ->get()
            ->mapWithKeys(function ($channel) {
                return [
                    $channel->name => [
                        'handler' => $channel->channel_class,
                        'config' => $channel->config ?? [],
                        'is_active' => $channel->status
                    ]
                ];
            })
            ->toArray();

        return array_merge($this->channels, $dbChannels);
    }

    public function disableChannel(string $name): void
    {
        // Update in-memory
        if (isset($this->channels[$name])) {
            $this->channels[$name]['is_active'] = false;
        }

        // Update database
        NotificationChannel::where('name', $name)->update(['status' => false]);
    }

    public function enableChannel(string $name): void
    {
        // Update in-memory
        if (isset($this->channels[$name])) {
            $this->channels[$name]['is_active'] = true;
        }

        // Update database
        NotificationChannel::where('name', $name)->update(['status' => true]);
    }

    protected function updateChannelInDatabase(
        string $name,
        string $handlerClass,
        array $config = [],
        bool $isActive = true
    ): void {
        NotificationChannel::updateOrCreate(
            ['name' => $name],
            [
                'channel_class' => $handlerClass,
                'config' => $config,
                'status' => $isActive
            ]
        );
    }

    public function syncFromDatabase(): void
    {
        NotificationChannel::all()->each(function ($channel) {
            $this->channels[$channel->name] = [
                'handler' => $channel->channel_class,
                'config' => $channel->config ?? [],
                'is_active' => $channel->status
            ];
        });
    }
}
