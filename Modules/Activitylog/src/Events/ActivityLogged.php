<?php

namespace Modules\Activitylog\Events;

use Modules\Activitylog\Models\Activity;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ActivityLogged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Activity $activity;

    /** 
     * Create a new event instance.
     */
    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
        logger('Broadcasting ActivityLogged event:', [$activity]);

        Log::info('ActivityLogged event fired', [
            'activity_id' => $activity->id,
            'description' => $activity->description,
            'causer_id' => $activity->causer_id,
            'causer_type' => $activity->causer_type,
            'subject_id' => $activity->subject_id,
            'created_at' => $activity->created_at->toDateTimeString(),
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Ensure causer_id exists before creating a private channel
        if ($this->activity->causer_id) {
            return [
                new PrivateChannel('activity-logs.1'),
            ];
        }

        // Fallback or handle activities without a specific causer_id if needed
        // For example, broadcast system activities to a general channel
        return [
            new Channel('activity-logs'), // Keep general channel for system activities or if causer_id is null
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'activity.logged';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $causer = $this->activity->causer;
        $userName = $causer ? $causer->name : 'System';
        $userAvatar = $causer && method_exists($causer, 'getAvatarUrl') ? $causer->getAvatarUrl() : null;
        $userInitials = $causer ? strtoupper(substr($causer->name, 0, 1)) : 'S';

        return [
            'id' => $this->activity->id,
            'log_name' => $this->activity->log_name,
            'description' => $this->activity->description,
            'subject_type' => $this->activity->subject_type,
            'subject_id' => $this->activity->subject_id,
            'causer_type' => $this->activity->causer_type,
            'causer_id' => $this->activity->causer_id,
            'properties' => $this->activity->properties,
            'event' => $this->activity->event,
            'created_at' => $this->activity->created_at->diffForHumans(), // For display
            'ip_address' => $this->activity->properties['ip_address'] ?? 'N/A',
            'user_agent' => $this->activity->properties['user_agent'] ?? 'N/A',
            'location' => $this->activity->properties['location'] ?? 'N/A',
            'severity' => $this->activity->properties['severity'] ?? 'info',
            'category' => $this->activity->properties['category'] ?? 'general',

            // Additional properties for frontend rendering
            'user_name' => $userName,
            'user_avatar' => $userAvatar,
            'user_initials' => $userInitials,
            'avatar_color' => $userAvatar ? null : 'bg-blue-500', // Default color if no avatar
            'title' => $this->activity->properties['title'] ?? $this->activity->description, // Use title from properties or description
            'tags' => $this->activity->properties['tags'] ?? [], // Assuming tags are an array in properties
            'formatted_date' => $this->activity->created_at->format('F j, Y H:i'),
            'full_description' => $this->activity->properties['full_description'] ?? $this->activity->description,
            'full_date' => $this->activity->created_at->format('l, F j, Y \a\t H:i:s A'),
            'relative_time' => $this->activity->created_at->diffForHumans(),
            'entity_id' => $this->activity->properties['entity_id'] ?? 'N/A',
            'entity_type' => $this->activity->properties['entity_type'] ?? 'N/A',
            'action_type' => $this->activity->properties['action_type'] ?? 'N/A',
        ];
    }
}
