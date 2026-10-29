<?php

namespace Modules\KYC\Services;

use Modules\KYC\Models\KycRequest;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycMessage;
use Modules\Acl\Models\Admin;
use Modules\Notifications\Services\NotificationService;
use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class KycNotificationService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Send KYC request notification to a user
     */
    public function sendKycRequestNotification(int $userId, KycRequest $request, string $userType = 'restaurant_owner'): void
    {
        try {
            // Get the restaurant owner details
            $user = $this->getNotifiable($userId, $userType);
            if (!$user) {
                Log::warning("KYC Notification: User not found", ['user_id' => $userId, 'type' => $userType]);
                return;
            }

            $userName = $user->name ?? 'Restaurant Owner';
            $data = [
                'title' => 'KYC Document Request',
                'user_name' => $userName,
                'owner_name' => $userName,
                'owner_name' => $userName, // legacy alias for older templates
                'kyc_type_name' => $request->kycType->name ?? 'KYC Documents',
                'request_message' => $request->message ?? 'KYC documents are required for verification. Please submit the necessary documents at your earliest convenience.',
                'due_date' => $request->due_date ? $request->due_date->format('F j, Y') : null,
                'company_name' => config('app.name', 'Our Company'),
                'current_year' => date('Y'),
                'data' => [
                    'request_id' => $request->id,
                ],
            ];

            // Trigger notification via standard pattern
            $this->notificationService->trigger(
                'KYC',
                'KycRequest',
                $user,
                $data
            );
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            Log::error("KYC Request Notification failed: " . $e->getMessage(), [
                'user_id' => $userId,
                'request_id' => $request->id
            ]);
        }
    }

    /**
     * Send a consolidated pending KYC notification to a restaurant owner.
     */
    public function sendRestaurantOwnerPendingKycRequestsNotification(Admin $user, array $requests): void
    {
        try {
            if (!$user) {
                Log::warning('KYC Pending Notification: Restaurant Owner not found');
                return;
            }

            $pendingTypes = collect($requests)
                ->filter(fn ($request) => $request instanceof KycRequest)
                ->map(fn (KycRequest $request) => $request->kycType?->name ?? 'KYC Documents')
                ->filter()
                ->values()
                ->all();

            if (empty($pendingTypes)) {
                return;
            }

            $data = [
                'owner_name' => $user->name ?? 'Restaurant Owner',
                'owner_name' => $user->name ?? 'Restaurant Owner', // legacy alias for older templates
                'user_name' => $user->name ?? 'Restaurant Owner',
                'pending_kyc_types' => $pendingTypes,
                'pending_requests_count' => count($pendingTypes),
                'company_name' => config('app.name', 'Our Company'),
                'current_year' => date('Y'),
            ];

            $this->notificationService->trigger('KYC', 'KycRequest', $user, $data);
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            Log::error('KYC Pending Request Notification failed: ' . $e->getMessage(), [
                'owner_id' => $user->id ?? null,
            ]);
        }
    }

    /**
     * Send KYC request notification to a group
     */
    public function sendKycRequestNotificationToGroup(string $group, KycRequest $request): void
    {
        // Get users based on group
        $userIds = $this->getUserIdsByGroup($group);
        foreach ($userIds as $userId) {
            $this->sendKycRequestNotification($userId, $request);
        }
    }

    /**
     * Send KYC status update notification
     */
    // public function sendKycStatusNotification(KycSubmission $submission): void
    // {
    //     try {
    //         $statusMessages = [
    //             'under_review' => 'Your KYC submission is under review.',
    //             'approved' => 'Your KYC submission has been approved.',
    //             'rejected' => 'Your KYC submission has been rejected. Please check the reason and resubmit.'
    //         ];

    //         $user = $submission->user;
    //         if (!$user) {
    //             Log::warning("KYC Status Notification: User not found", ['submission_id' => $submission->id]);
    //             return;
    //         }

    //         $data = [
    //             'user_name' => $user->name ?? 'User',
    //             'owner_name' => $user->name ?? 'User', // For backward compatibility
    //             'kyc_type_name' => $submission->kycType->name ?? 'KYC Documents',
    //             'status' => $submission->status,
    //             'status_message' => $statusMessages[$submission->status] ?? 'Your KYC submission status has been updated.',
    //             'rejection_reason' => $submission->rejection_reason,
    //             'company_name' => config('app.name', 'Our Company'),
    //             'current_year' => date('Y')
    //         ];

    //         // Trigger notification via standard pattern
    //         $this->notificationService->trigger(
    //             'KYC',
    //             'KycStatus',
    //             $user,
    //             $data
    //         );

    //     } catch (\Exception $e) {
    //         Log::error("KYC Status Notification failed: " . $e->getMessage(), [
    //             'submission_id' => $submission->id
    //         ]);
    //     }
    // }

    public function sendKycStatusNotification(KycSubmission $submission): void
    {
        try {
            $owner = $submission->owner;
            if (!$owner) {
                Log::warning('KYC Status Notification: Restaurant Owner not found', [
                    'submission_id' => $submission->id
                ]);
                return;
            }
            $data = [
                'owner_name' => $owner->name,
                'owner_name' => $owner->name,
                'kyc_type_name' => $submission->kycType->name ?? 'KYC Documents',
                'document_type' => $submission->kycType->name ?? 'KYC Documents',
                'status' => $submission->status,
                'status_message' => $this->getStatusMessage($submission->status),
                'rejection_reason' => $submission->rejection_reason,
                'reviewed_at' => $submission->reviewed_at?->format('Y-m-d H:i:s'),
                'company_name' => config('app.name', 'Our Company'),
                'current_year' => date('Y')
            ];
            $this->notificationService->trigger('KYC', 'KycStatus', $owner, $data);
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            Log::error('KYC Status Notification failed: ' . $e->getMessage(), [
                'submission_id' => $submission->id
            ]);
        }
    }

    /**
     * Sent once when onboarding KYC is fully approved (all required documents).
     */
    public function sendOnboardingApprovedNotification($owner): void
    {
        try {
            if (! $owner) {
                return;
            }

            $data = [
                'owner_name' => $owner->name ?? 'Restaurant Owner',
                'user_name' => $owner->name ?? 'Restaurant Owner',
                'kyc_type_name' => 'Onboarding',
                'document_type' => 'Onboarding',
                'status' => 'approved',
                'status_message' => 'Your onboarding has been completed successfully. Your restaurant account is now active.',
                'notification_type' => 'onboarding_success',
                'reviewed_at' => now()->toDateTimeString(),
                'rejection_reason' => '',
                'company_name' => config('app.name', 'Our Company'),
                'current_year' => date('Y'),
                'data' => [
                    'owner_id' => $owner->id,
                ],
            ];

            $this->notificationService->trigger('KYC', 'KycStatus', $owner, $data);
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            Log::error('Restaurant Owner onboarding approved notification failed: ' . $e->getMessage(), [
                'owner_id' => $owner->id ?? null,
            ]);
        }
    }

    /** @deprecated Use sendOnboardingApprovedNotification() */
    public function sendOwnerApprovedNotification($owner): void
    {
        $this->sendOnboardingApprovedNotification($owner);
    }


    protected function getStatusMessage(string $status): string
    {
        return match ($status) {
            'under_review' => 'Your KYC submission is under review.',
            'approved' => 'Your KYC submission has been approved.',
            'rejected' => 'Your KYC submission has been rejected. Please check the reason and resubmit.',
            default => 'Your KYC submission status has been updated.'
        };
    }

    /**
     * Send new message notification
     */
    public function sendNewMessageNotification(KycSubmission $submission, KycMessage $message): void
    {
        try {
            $owner = $submission->owner ?? $submission->user;
            if (!$owner) {
                Log::warning('KYC Message Notification: Restaurant Owner not found', ['submission_id' => $submission->id]);
                return;
            }

            $ownerName = $owner->name ?? 'Restaurant Owner';
            $data = [
                'owner_name' => $ownerName,
                'kyc_type_name' => $submission->kycType->name ?? 'KYC Documents',
                'message_content' => $message->message,
                'submission_url' => url('/restaurant/kyc/history'),
                'company_name' => config('app.name', 'Our Company'),
                'current_year' => date('Y'),
                'data' => [
                    'submission_id' => $submission->id,
                    'message_id' => $message->id,
                ],
            ];

            // Trigger notification via standard pattern
            $this->notificationService->trigger(
                'KYC',
                'KycMessage',
                $owner,
                $data
            );
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            Log::error('KYC Message Notification failed: ' . $e->getMessage(), [
                'submission_id' => $submission->id,
                'message_id' => $message->id
            ]);
        }
    }

    /**
     * Get notifiable model (Owner or User)
     */
    protected function getNotifiable(int $userId, string $userType)
    {
        $normalizedType = strtolower(trim((string) $userType));

        if (in_array($normalizedType, ['owner', 'restaurant_owner', 'admin', 'user'])) {
            return Admin::find($userId);
        }

        return null;
    }

    /**
     * Get user IDs by group
     */
    protected function getUserIdsByGroup(string $group): array
    {
        // This would fetch users based on group
        // Implementation depends on your user group structure
        switch ($group) {
            case 'restaurants':
            case 'all_restaurants':
                return Admin::query()
                    ->join('roles', 'admins.role_id', '=', 'roles.id')
                    ->where('roles.guard', 'admin')
                    ->where('roles.name', 'Restaurant Owner')
                    ->where('admins.status', 1)
                    ->pluck('admins.id')
                    ->toArray();
            case 'all_users':
                return Admin::query()->where('status', 1)->pluck('id')->toArray();
            default:
                return [];
        }
    }
}
