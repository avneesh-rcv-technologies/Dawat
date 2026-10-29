<?php

namespace Modules\KYC\Services;

use Modules\KYC\Models\KycRequest;
use Modules\KYC\Models\KycType;
use Modules\KYC\Exceptions\KycException;
use Modules\KYC\Traits\HandlesKycErrors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use Modules\Acl\Models\Admin;

class KycRequestService
{
    use HandlesKycErrors;
    /**
     * Get all KYC requests
     */
    public function getRequests(array $filters = []): Collection
    {
        try {
            $this->logKycOperation('Getting KYC requests', ['filters' => $filters]);

            $query = KycRequest::with(['kycType', 'requestedBy']);

            if (!empty($filters['kyc_type_id'])) {
                // Validate KYC type exists
                if (!KycType::find($filters['kyc_type_id'])) {
                    throw KycException::typeNotFound($filters['kyc_type_id']);
                }
                $query->where('kyc_type_id', $filters['kyc_type_id']);
            }

            if (!empty($filters['status'])) {
                $validStatuses = ['overdue', 'upcoming'];
                if (!in_array($filters['status'], $validStatuses)) {
                    throw new KycException(
                        "Invalid status filter: {$filters['status']}",
                        'INVALID_STATUS_FILTER',
                        422,
                        ['valid_statuses' => $validStatuses]
                    );
                }

                if ($filters['status'] === 'overdue') {
                    $query->overdue();
                } elseif ($filters['status'] === 'upcoming') {
                    $query->upcoming();
                }
            }

            return $query->orderBy('created_at', 'desc')->get();
        } catch (Exception $e) {
            Log::error('Failed to get KYC requests', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a new KYC request
     */
    public function createRequest(array $data, int $adminId): KycRequest
    {
        try {
            $this->validateKycParameters(['kyc_type_id', 'title'], $data);

            // Validate KYC type exists and is active
            $kycType = KycType::find($data['kyc_type_id']);
            if (!$kycType) {
                throw KycException::typeNotFound($data['kyc_type_id']);
            }

            if (!$kycType->is_active) {
                throw new KycException(
                    'Cannot create request for inactive KYC type',
                    'INACTIVE_KYC_TYPE',
                    422,
                    ['kyc_type_id' => $data['kyc_type_id']]
                );
            }

            $this->logKycOperation('Creating KYC request', [
                'kyc_type_id' => $data['kyc_type_id'],
                'admin_id' => $adminId,
                'title' => $data['title']
            ]);

            return DB::transaction(function () use ($data, $adminId) {
                $request = KycRequest::create([
                    'kyc_type_id' => $data['kyc_type_id'],
                    'requested_by' => $adminId,
                    'title' => $data['title'],
                    'message' => $data['message'] ?? null,
                    'due_date' => $data['due_date'] ?? null
                ]);

                // // Send notifications based on target
                // if (!empty($data['target'])) {
                //     $this->sendNotifications($request, $data['target']);
                // }

                return $request->fresh(['kycType', 'requestedBy']);
            });
        } catch (Exception $e) {
            Log::error('Failed to create KYC request', [
                'data' => $data,
                'admin_id' => $adminId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send notifications for KYC request
     */
    protected function sendNotifications(KycRequest $request, array $target): void
    {
        try {
            $this->validateKycParameters(['type'], $target);

            $notificationService = app(KycNotificationService::class);

            switch ($target['type']) {
                case 'single':
                    if (empty($target['user_id'])) {
                        throw new KycException(
                            'User ID is required for single notification',
                            'MISSING_USER_ID',
                            422
                        );
                    }
                    $userType = $target['user_type'] ?? 'restaurant_owner';
                    $notificationService->sendKycRequestNotification(
                        $target['user_id'],
                        $request,
                        $userType
                    );
                    break;

                case 'multiple':
                    if (empty($target['user_ids']) || !is_array($target['user_ids'])) {
                        throw new KycException(
                            'User IDs array is required for multiple notifications',
                            'MISSING_USER_IDS',
                            422
                        );
                    }
                    foreach ($target['user_ids'] as $index => $userId) {
                        $userType = $target['user_types'][$index] ?? 'restaurant_owner';
                        $notificationService->sendKycRequestNotification(
                            $userId,
                            $request,
                            $userType
                        );
                    }
                    break;

                case 'group':
                    if (empty($target['group'])) {
                        throw new KycException(
                            'Group is required for group notification',
                            'MISSING_GROUP',
                            422
                        );
                    }
                    $notificationService->sendKycRequestNotificationToGroup(
                        $target['group'],
                        $request
                    );
                    break;

                case 'all':
                    $notificationService->sendKycRequestNotificationToGroup(
                        'all_restaurants',
                        $request
                    );
                    break;

                default:
                    throw new KycException(
                        "Invalid notification target type: {$target['type']}",
                        'INVALID_TARGET_TYPE',
                        422,
                        ['valid_types' => ['single', 'multiple', 'group', 'all']]
                    );
            }
        } catch (Exception $e) {
            Log::error('Failed to send KYC request notifications', [
                'request_id' => $request->id,
                'target' => $target,
                'error' => $e->getMessage()
            ]);

            // Don't throw notification errors, just log them
            if (!($e instanceof KycException)) {
                throw KycException::notificationFailed($e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * Delete a KYC request
     */
    public function deleteRequest(int $id): bool
    {
        try {
            $this->logKycOperation('Deleting KYC request', ['request_id' => $id]);

            $request = KycRequest::find($id);

            if (!$request) {
                throw KycException::requestNotFound($id);
            }

            // Check if request has submissions
            $submissionsCount = $request->kycType->submissions()->count();
            if ($submissionsCount > 0) {
                throw new Exception(
                    "Cannot delete this KYC request. There " .
                        ($submissionsCount === 1 ? "is 1 submission" : "are {$submissionsCount} submissions") .
                        " associated with it."
                );
            }

            return $request->delete();
        } catch (Exception $e) {
            Log::error('Failed to delete KYC request', [
                'request_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get request statistics
     */
    public function getStatistics(): array
    {
        try {
            $this->logKycOperation('Getting KYC request statistics');

            return [
                'total' => KycRequest::count(),
                'upcoming' => KycRequest::upcoming()->count(),
                'overdue' => KycRequest::overdue()->count(),
                'this_week' => KycRequest::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count()
            ];
        } catch (Exception $e) {
            Log::error('Failed to get KYC request statistics', [
                'error' => $e->getMessage()
            ]);
            throw new KycException(
                'Failed to retrieve statistics',
                'STATISTICS_ERROR',
                500
            );
        }
    }

    /**
     * Create pending KYC requests for an admin (restaurant owner) for all active KYC types.
     * Returns an array of created KycRequest instances.
     *
     * @param Admin $admin
     * @param int|null $requestedBy
     * @return array
     */
    public function createPendingRequestsForAdmin(Admin $admin, ?int $requestedBy = null): array
    {
        $created = [];
        try {
            $kycTypes = KycType::query()
                ->where('is_active', true)
                ->where('type', 'kyc')
                ->orderBy('id')
                ->get();

            foreach ($kycTypes as $kycType) {
                try {
                    $req = $this->createRequest([
                        'kyc_type_id' => $kycType->id,
                        'title' => 'Please complete your KYC verification',
                        'message' => 'Your restaurant account requires KYC verification before activation.',
                        'due_date' => now()->addDays(7),
                        'target' => [
                            'type' => 'single',
                            'user_id' => $admin->id,
                            'user_type' => 'admin',
                        ],
                    ], $requestedBy ?? $admin->id);

                    $created[] = $req;
                } catch (Exception $e) {
                    Log::warning('Restaurant KYC request creation failed for type', [
                        'admin_id' => $admin->id,
                        'kyc_type_id' => $kycType->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to create pending KYC requests', ['admin_id' => $admin->id ?? null, 'error' => $e->getMessage()]);
        }

        return $created;
    }
}
