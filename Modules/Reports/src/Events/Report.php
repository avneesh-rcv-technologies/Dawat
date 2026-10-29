<?php

namespace Modules\Reports\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Backend\Helpers\BroadcastChannelToken;
use Modules\Backend\Support\QueueSyncState;


class Report implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $data)
    {
        $this->data = $data;
    }

     public function broadcastOn(): array
    {
        // Ensure we have the creator details
        $dataArray = is_object($this->data) && method_exists($this->data, 'toArray')
            ? $this->data->toArray()
            : (array) $this->data;

            
        $creatorId = $dataArray['creator_id'] ?? null;
        $creatorType = $dataArray['creator_type'] ?? null;

        
        if(!$creatorId || !$creatorType) {            
            return [];
        }

        $channelName = BroadcastChannelToken::channelName((int) $creatorId, $creatorType, 'report-logs');

        // Create a channel unique to the creator
        return [
            new PrivateChannel($channelName),
        ];
    }


    public function broadcastAs(): string
    {
        return 'report-logged';
    }

    public function broadcastWith(): array
    {
        $essentialData = $this->extractEssentialData($this->data);

        $creatorId = isset($essentialData['creator_id']) ? (int) $essentialData['creator_id'] : null;
        $creatorType = $essentialData['creator_type'] ?? null;

        if (!isset($essentialData['sync_state'])) {
            $essentialData['sync_state'] = QueueSyncState::get($creatorId, $creatorType);
        }

        return [
            'data' => $essentialData,
        ];
    }

    /**
     * Extract only the data needed for notifications and modals
     */
    private function extractEssentialData($data): array {
        // Handle both model objects and arrays
        return is_object($data) && method_exists($data, 'toArray') ? $data->toArray() : (array) $data;
    }
}
