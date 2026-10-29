<?php

namespace Modules\Acl\Services;

use Modules\Acl\Models\SupportMessage;
use Illuminate\Support\Facades\DB;

class SupportAdminService
{
    /**
     * Find support message by ID
     */
    public function find($id): ?SupportMessage
    {
        return SupportMessage::with(['customer', 'order'])->find($id);
    }

    /**
     * Update support message status
     */
    public function updateStatus($id, string $status): SupportMessage
    {
        $message = $this->find($id);
        
        if (!$message) {
            throw new \Exception('Support message not found');
        }

        $message->update(['status' => $status]);

        if ($status === SupportMessage::STATUS_REPLIED) {
            $message->update(['replied_at' => now()]);
        }

        return $message->fresh();
    }

    /**
     * Update multiple message statuses
     */
    public function updateStatuses(array $ids, string $status): int
    {
        $data = ['status' => $status];
        
        if ($status === SupportMessage::STATUS_REPLIED) {
            $data['replied_at'] = now();
        }

        return SupportMessage::whereIn('id', $ids)->update($data);
    }

    /**
     * Delete support message
     */
    public function delete($id): bool
    {
        $message = $this->find($id);
        
        if (!$message) {
            throw new \Exception('Support message not found');
        }

        return $message->delete();
    }

    /**
     * Delete multiple messages
     */
    public function deleteMultiple(array $ids): int
    {
        return SupportMessage::whereIn('id', $ids)->delete();
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => SupportMessage::count(),
            'new' => SupportMessage::where('status', SupportMessage::STATUS_NEW)->count(),
            'read' => SupportMessage::where('status', SupportMessage::STATUS_READ)->count(),
            'replied' => SupportMessage::where('status', SupportMessage::STATUS_REPLIED)->count(),
            'closed' => SupportMessage::where('status', SupportMessage::STATUS_CLOSED)->count(),
            'today' => SupportMessage::whereDate('created_at', today())->count(),
            'this_week' => SupportMessage::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => SupportMessage::whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Get messages by status
     */
    public function getByStatus(string $status, int $perPage = 10)
    {
        return SupportMessage::with(['customer', 'order'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get messages by category
     */
    public function getByCategory(string $category, int $perPage = 10)
    {
        return SupportMessage::with(['customer', 'order'])
            ->where('category', $category)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search messages
     */
    public function search(string $query, int $perPage = 10)
    {
        return SupportMessage::with(['customer', 'order'])
            ->where(function ($q) use ($query) {
                $q->where('reference_number', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('subject', 'like', "%{$query}%")
                    ->orWhere('message', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
