<?php

namespace Modules\KYC\Services;

use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycSubmissionData;
use Modules\KYC\Models\KycFile;
use Modules\KYC\Models\KycType;
use Modules\KYC\Exceptions\KycException;
use Modules\KYC\Traits\HandlesKycErrors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Modules\Activitylog\Services\ActivityLoggerService;
use Modules\Acl\Models\Admin;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Services\KycOnboardingStepService;
use Exception;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class KycSubmissionService
{
    use HandlesKycErrors;
    /**
     * Get all submissions with filters
     */
    public function getSubmissions(array $filters = []): Collection
    {
        $query = KycSubmission::with(['kycType', 'reviewer', 'data.field']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['kyc_type_id'])) {
            $query->where('kyc_type_id', $filters['kyc_type_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a single submission with all data
     */
    public function getSubmission(int $id): KycSubmission
    {
        try {
            $this->logKycOperation('Getting KYC submission', ['submission_id' => $id]);

            $submission = KycSubmission::with([
                'kycType.fields',
                'data.field',
                'files',
                'messages',
                'reviewer'
            ])->find($id);

            if (!$submission) {
                throw KycException::submissionNotFound($id);
            }

            return $submission;
        } catch (Exception $e) {
            Log::error('Failed to get KYC submission', [
                'submission_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a new submission
     */
    public function createSubmission(int $userId, int $kycTypeId, array $data, bool $allowUnapprovedReplacement = false): KycSubmission
    {
        try {
            $this->logKycOperation('Creating KYC submission', [
                'user_id' => $userId,
                'kyc_type_id' => $kycTypeId
            ]);

            // Validate KYC type exists and is active
            $kycType = KycType::with('fields')->find($kycTypeId);
            if (!$kycType) {
                throw KycException::typeNotFound($kycTypeId);
            }

            if (!$kycType->is_active) {
                throw new KycException(
                    'Cannot submit to inactive KYC type',
                    'INACTIVE_KYC_TYPE',
                    422,
                    ['kyc_type_id' => $kycTypeId]
                );
            }

            $this->validateFieldData($kycType, $data);

            // PRE-VALIDATION: Validate all fields first to prevent deleting existing data if the new request is invalid
            foreach ($kycType->fields as $field) {
                $fieldKey = $field->field_key;
                $fieldValue = $data[$fieldKey] ?? null;

                if ($field->is_required && ($fieldValue === null || $fieldValue === '')) {
                    throw new KycException(
                        "Required field '{$field->field_name}' is missing.",
                        'REQUIRED_FIELD_MISSING',
                        422,
                        ['field_key' => $fieldKey, 'field_name' => $field->field_name]
                    );
                }

                if ($field->isFileType()) {
                    if (isset($data[$fieldKey]) && !($data[$fieldKey] instanceof UploadedFile)) {
                        throw KycException::fileUploadFailed("Field '{$field->field_name}' expects a file, but text was provided.");
                    }
                } else {
                    if (isset($data[$fieldKey]) && $data[$fieldKey] instanceof UploadedFile) {
                        throw new KycException(
                            "Field '{$field->field_name}' expects text, but a file was uploaded.",
                            'INVALID_DATA_TYPE',
                            422,
                            ['field_key' => $fieldKey, 'field_name' => $field->field_name]
                        );
                    }
                }
            }

            // Check if user already has an existing submission for this type
            $existingSubmission = KycSubmission::with('files')->where('user_id', $userId)
                ->where('kyc_type_id', $kycTypeId)
                ->whereIn('status', ['pending', 'under_review', 'rejected', 'approved'])
                ->first();

            if ($existingSubmission) {
                // Explicit onboarding reuploads may replace any unapproved submission.
                if ($existingSubmission->status === 'approved' || (!$allowUnapprovedReplacement && $existingSubmission->status !== 'rejected')) {
                    throw new KycException(
                        "Cannot submit new document for this type because an existing submission is in status '{$existingSubmission->status}'.",
                        'DUPLICATE_SUBMISSION',
                        422,
                        [
                            'existing_submission_id' => $existingSubmission->id,
                            'status' => $existingSubmission->status
                        ]
                    );
                }
            }

            return DB::transaction(function () use ($userId, $kycTypeId, $data, $kycType, $existingSubmission) {
                $oldPaths = [];
                if ($existingSubmission) {
                    $oldPaths = $existingSubmission->files->pluck('file_path')->all();
                    $existingSubmission->data()->delete();
                    $existingSubmission->files()->delete();
                    $existingSubmission->messages()->delete();
                    $existingSubmission->delete();
                }
                // Create submission
                $submission = KycSubmission::create([
                    'user_id' => $userId,
                    'kyc_type_id' => $kycTypeId,
                    'status' => 'pending'
                ]);

                // Log KYC submission creation
                // ActivityLoggerService::logContent(
                //     "New KYC submission created",
                //     $submission,
                //     [
                //         'user_id' => $userId,
                //         'kyc_type_id' => $kycTypeId,
                //         'kyc_type_name' => $kycType->name,
                //         'submission_id' => $submission->id,
                //         'event' => 'kyc_submission_created',
                //         'severity' => 'info'
                //     ]
                // );

                // Process each field and save data
                foreach ($kycType->fields as $field) {
                    $fieldKey = $field->field_key;
                    $fieldValue = $data[$fieldKey] ?? null;

                    if ($field->isFileType() && isset($data[$fieldKey])) {
                        // Handle file upload
                        $this->handleFileUpload($submission, $field, $data[$fieldKey]);
                        $fieldValue = 'file_uploaded';
                    }

                    // Create submission data
                    KycSubmissionData::create([
                        'submission_id' => $submission->id,
                        'field_id' => $field->id,
                        'field_key' => $fieldKey,
                        'field_value' => $fieldValue
                    ]);
                }

                // A failed replacement must leave the original files available.
                if ($oldPaths) {
                    DB::afterCommit(function () use ($oldPaths) {
                        Storage::disk('public')->delete($oldPaths);
                    });
                }
                return $submission->fresh(['data', 'files']);
            });
        } catch (Exception $e) {
            Log::error('Failed to create KYC submission', [
                'user_id' => $userId,
                'kyc_type_id' => $kycTypeId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create multiple submissions at once, enforcing all active documents are provided.
     */
    public function createMultipleSubmissions(int $userId, array $documents): array
    {
        try {
            $this->logKycOperation('Creating Multiple KYC submissions', ['user_id' => $userId]);

            // 1. Fetch all active KYC types
            $activeKycTypes = KycType::with('fields')->where('is_active', true)->get();
            $activeKycTypeIds = $activeKycTypes->pluck('id')->toArray();

            // 2. Validate that ALL active KYC types are present in the request
            $submittedKycTypeIds = array_map(fn($doc) => (int)($doc['kyc_type_id'] ?? 0), $documents);
            $missingKycTypes = array_diff($activeKycTypeIds, $submittedKycTypeIds);

            if (!empty($missingKycTypes)) {
                $missingNames = $activeKycTypes->whereIn('id', $missingKycTypes)->pluck('name')->implode(', ');
                throw new KycException(
                    "You must upload all required KYC documents at once. Missing: {$missingNames}",
                    'MISSING_REQUIRED_DOCUMENTS',
                    422
                );
            }

            // 3. Pre-validate every single field for all submitted documents
            $existingSubmissionsToDelete = [];

            foreach ($documents as $docData) {
                $kycTypeId = (int)($docData['kyc_type_id'] ?? 0);
                $kycType = $activeKycTypes->firstWhere('id', $kycTypeId);

                if (!$kycType) {
                    continue; // Will be caught by general validation or just ignored if inactive
                }

                $this->validateFieldData($kycType, $docData);

                foreach ($kycType->fields as $field) {
                    $fieldKey = $field->field_key;
                    $fieldValue = $docData[$fieldKey] ?? null;

                    if ($field->is_required && ($fieldValue === null || $fieldValue === '')) {
                        throw new KycException(
                            "Required field '{$field->field_name}' in '{$kycType->name}' is missing.",
                            'REQUIRED_FIELD_MISSING',
                            422
                        );
                    }

                    if ($field->isFileType()) {
                        if (isset($docData[$fieldKey]) && !($docData[$fieldKey] instanceof UploadedFile)) {
                            throw KycException::fileUploadFailed("Field '{$field->field_name}' in '{$kycType->name}' expects a file.");
                        }
                    } else {
                        if (isset($docData[$fieldKey]) && $docData[$fieldKey] instanceof UploadedFile) {
                            throw new KycException(
                                "Field '{$field->field_name}' in '{$kycType->name}' expects text, but a file was uploaded.",
                                'INVALID_DATA_TYPE',
                                422
                            );
                        }
                    }
                }

                // Check existing submissions
                $existingSubmission = KycSubmission::with('files')->where('user_id', $userId)
                    ->where('kyc_type_id', $kycTypeId)
                    ->whereIn('status', ['pending', 'under_review', 'rejected', 'approved'])
                    ->first();

                if ($existingSubmission) {
                    // Only allow replacing rejected submissions during bulk upload
                    if ($existingSubmission->status !== 'rejected') {
                        throw new KycException(
                            "Cannot re-upload KYC already status '{$existingSubmission->status}'.",
                            'DUPLICATE_SUBMISSION',
                            422
                        );
                    }
                    $existingSubmissionsToDelete[] = $existingSubmission;
                }
            }

            // 4. Now that EVERYTHING is perfectly valid, delete old files
            foreach ($existingSubmissionsToDelete as $existingSubmission) {
                foreach ($existingSubmission->files as $file) {
                    if (Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }
                }
                $existingSubmission->data()->delete();
                $existingSubmission->files()->delete();
                $existingSubmission->messages()->delete();
                $existingSubmission->delete();
            }

            // 5. Finally, insert all records inside a transaction
            $createdSubmissions = DB::transaction(function () use ($userId, $documents, $activeKycTypes) {
                $createdSubmissions = [];

                foreach ($documents as $docData) {
                    $kycTypeId = (int)($docData['kyc_type_id'] ?? 0);
                    $kycType = $activeKycTypes->firstWhere('id', $kycTypeId);

                    if (!$kycType) continue;

                    $submission = KycSubmission::create([
                        'user_id' => $userId,
                        'kyc_type_id' => $kycTypeId,
                        'status' => 'pending'
                    ]);

                    foreach ($kycType->fields as $field) {
                        $fieldKey = $field->field_key;
                        $fieldValue = $docData[$fieldKey] ?? null;

                        if ($field->isFileType() && isset($docData[$fieldKey])) {
                            $this->handleFileUpload($submission, $field, $docData[$fieldKey]);
                            $fieldValue = 'file_uploaded';
                        }

                        KycSubmissionData::create([
                            'submission_id' => $submission->id,
                            'field_id' => $field->id,
                            'field_key' => $fieldKey,
                            'field_value' => $fieldValue
                        ]);
                    }

                    $createdSubmissions[] = $submission->fresh(['data', 'files']);
                }

                return $createdSubmissions;
            });

            if (!empty($createdSubmissions)) {
                $owner = Admin::find($userId);
                if ($owner) {
                    $documentTypes = collect($createdSubmissions)
                        ->map(fn (KycSubmission $submission) => $submission->kycType->name ?? 'KYC Document')
                        ->unique()
                        ->values()
                        ->all();

                    $notificationService = app(\Modules\Notifications\Services\NotificationService::class);
                    $notificationService->trigger('KYC', 'KycDocumentSubmission', $owner, [
                        'title' => 'KYC Documents Submitted',
                        'owner_name' => $owner->name,
                        'document_types' => $documentTypes,
                        'submission_count' => count($createdSubmissions),
                        'submission_url' => url('/restaurant/kyc/history'),
                        'request_message' => 'Your KYC documents have been submitted successfully. Our team will review them shortly.',
                        'company_name' => config('app.name', 'Our Company'),
                        'current_year' => date('Y'),
                        'data' => [
                            'submission_ids' => collect($createdSubmissions)->pluck('id')->all(),
                        ],
                    ]);
                }

                // Update restaurant onboarding status to 'submitted' when multiple documents uploaded
                // try {
                //     $restaurant = Restaurant::where('admin_id', $userId)->first();
                //     if ($restaurant) {
                //         $current = is_object($restaurant->onboarding_status) ? ($restaurant->onboarding_status->value ?? (string)$restaurant->onboarding_status) : $restaurant->onboarding_status;
                //         if ($current !== OnboardingStatus::SUBMITTED->value) {
                //             $restaurant->update(['onboarding_status' => OnboardingStatus::SUBMITTED->value]);
                //             Log::info('Restaurant onboarding_status updated to submitted after KYC multi-upload', ['restaurant_id' => $restaurant->id, 'user_id' => $userId]);
                //         }
                //     }
                // } catch (Exception $e) {
                //     Log::error('Failed to update restaurant onboarding_status to submitted', ['user_id' => $userId, 'error' => $e->getMessage()]);
                // }
            }

            return $createdSubmissions;
        } catch (Exception $e) {
            Log::error('Failed to create multiple KYC submissions', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($e instanceof KycException) {
                throw $e;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                throw KycException::validationFailed($e->errors());
            }

            throw KycException::validationFailed([
                'general' => [$e->getMessage()],
            ]);
        }
    }

    /**
     * Update a submission
     */
    // public function updateSubmission(int $id, array $data): KycSubmission
    // {
    //     return DB::transaction(function () use ($id, $data) {
    //         $submission = KycSubmission::findOrFail($id);

    //         if (!$submission->isEditable()) {
    //             throw new \Exception('Submission is not editable in current status');
    //         }

    //         // Update submission data
    //         foreach ($data as $fieldKey => $fieldValue) {
    //             $submissionData = $submission->data()
    //                 ->where('field_key', $fieldKey)
    //                 ->first();

    //             if ($submissionData) {
    //                 $field = $submissionData->field;

    //                 if ($field->isFileType() && $fieldValue instanceof UploadedFile) {
    //                     // Handle file upload
    //                     $this->handleFileUpload($submission, $field, $fieldValue);
    //                     $fieldValue = 'file_uploaded';
    //                 }

    //                 $submissionData->update(['field_value' => $fieldValue]);
    //             }
    //         }

    //         // Reset status to pending if it was rejected
    //         if ($submission->status === 'rejected') {
    //             $submission->update(['status' => 'pending']);
    //         }

    //         return $submission->fresh(['data', 'files']);
    //     });
    // }

    public function updateSubmission(int $id, array $data): KycSubmission
    {
        return DB::transaction(function () use ($id, $data) {
            $submission = KycSubmission::findOrFail($id);

            if (!$submission->isEditable()) {
                throw new \Exception('Submission is not editable in current status');
            }

            // Reset status to pending BEFORE file upload
            if ($submission->status === 'rejected') {
                $submission->update([
                    'status' => 'pending',
                    'rejection_reason' => null
                ]);
                $submission->refresh(); // Reload fresh data

                $owner = Admin::find($submission->user_id);
                if ($owner && $owner->onboarding_outcome === 'rejected') {
                    $remainingRejected = KycSubmission::where('user_id', $submission->user_id)
                        ->where('id', '!=', $submission->id)
                        ->where('status', 'rejected')
                        ->exists();

                    if (!$remainingRejected) {
                        $owner->update(['onboarding_outcome' => 'pending']);
                    }
                }

                Log::info('KYC status reset to pending before re-upload', [
                    'submission_id' => $id,
                    'user_id' => $submission->user_id,
                    'old_status' => 'rejected',
                    'new_status' => 'pending'
                ]);
            }

            //  process file uploads (status is already "pending")
            foreach ($data as $fieldKey => $fieldValue) {
                $submissionData = $submission->data()
                    ->where('field_key', $fieldKey)
                    ->first();

                if ($submissionData) {
                    $field = $submissionData->field;

                    if ($field->isFileType() && $fieldValue instanceof UploadedFile) {
                        // Handle file upload
                        $this->handleFileUpload($submission, $field, $fieldValue);
                        $fieldValue = 'file_uploaded';
                    }

                    $submissionData->update(['field_value' => $fieldValue]);
                }
            }

            return $submission->fresh(['data', 'files']);
        });
    }

    /**
     * Validate dynamic KYC fields using the rules configured by the admin.
     * Partial validation is used when an existing submission is being edited.
     */
    public function validateFieldData(KycType $kycType, array $data, bool $partial = false): void
    {
        $rules = [];
        $messages = [];

        foreach ($kycType->fields as $field) {
            $fieldKey = $field->field_key;

            if ($partial && (!array_key_exists($fieldKey, $data) || $data[$fieldKey] === null || $data[$fieldKey] === '')) {
                continue;
            }

            $rules[$fieldKey] = $field->getValidationRules();
            $messages = array_merge($messages, $field->getValidationMessages());
        }

        if (!$rules) {
            return;
        }

        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            log::error('KYC field validation failed', [
                'kyc_type_id' => $kycType->id,
                'user_id' => $data['user_id'] ?? null,
                'errors' => $validator->errors()->toArray()
            ]);
            throw KycException::validationFailed($validator->errors()->toArray());
        }
    }



    /**
     * Handle file upload
     */
    protected function handleFileUpload(KycSubmission $submission, $field, UploadedFile $file): KycFile
    {
        try {
            // Use field-level validation_rules if set, otherwise fall back to config defaults
            $stored      = $field->validation_rules ?? [];
            $maxSize     = $stored['max_size_kb'] ?? config('kyc.max_file_size', 10240);
            $allowedTypes = !empty($stored['allowed_types'])
                ? (is_array($stored['allowed_types'])
                    ? $stored['allowed_types']
                    : array_map('trim', explode(',', $stored['allowed_types'])))
                : config('kyc.allowed_file_types', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);

            if ($file->getSize() > $maxSize * 1024) {
                throw KycException::fileUploadFailed("File size exceeds maximum allowed size of {$maxSize}KB");
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $allowedTypes)) {
                throw KycException::fileUploadFailed("File type '{$extension}' is not allowed. Allowed types: " . implode(', ', $allowedTypes));
            }

            // Delete existing file if any
            $existingFile = KycFile::where('submission_id', $submission->id)
                ->where('field_id', $field->id)
                ->first();

            if ($existingFile) {
                if (Storage::disk('public')->exists($existingFile->file_path)) {
                    Storage::disk('public')->delete($existingFile->file_path);
                }
                $existingFile->delete();
            }

            // Store new file
            $path = $file->store('kyc/' . $submission->user_id . '/' . $submission->id, 'public');

            if (!$path) {
                throw KycException::fileUploadFailed('Failed to store file');
            }

            return KycFile::create([
                'submission_id' => $submission->id,
                'field_id' => $field->id,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('File upload failed', [
                'submission_id' => $submission->id,
                'field_id' => $field->id,
                'error' => $e->getMessage()
            ]);

            if ($e instanceof KycException) {
                throw $e;
            }

            throw KycException::fileUploadFailed($e->getMessage());
        }
    }

    /**
     * Review a submission
     */
    // public function reviewSubmission(int $id, int $adminId, string $action, string $reason = null): KycSubmission
    // {
    //     try {
    //         $this->logKycOperation('Reviewing KYC submission', [
    //             'submission_id' => $id,
    //             'admin_id' => $adminId,
    //             'action' => $action
    //         ]);

    //         $submission = KycSubmission::find($id);
    //         if (!$submission) {
    //             throw KycException::submissionNotFound($id);
    //         }

    //         // Validate submission can be reviewed
    //         if (!in_array($submission->status, ['pending', 'under_review'])) {
    //             throw new KycException(
    //                 "Submission cannot be reviewed in '{$submission->status}' status",
    //                 'INVALID_SUBMISSION_STATUS',
    //                 422,
    //                 ['current_status' => $submission->status, 'allowed_statuses' => ['pending', 'under_review']]
    //             );
    //         }

    //         $validActions = ['under_review', 'approve', 'reject'];
    //         if (!in_array($action, $validActions)) {
    //             throw KycException::invalidAction($action);
    //         }

    //         return DB::transaction(function () use ($submission, $adminId, $action, $reason) {
    //             switch ($action) {
    //                 case 'under_review':
    //                     if ($submission->status === 'under_review') {
    //                         throw new KycException(
    //                             'Submission is already under review',
    //                             'ALREADY_UNDER_REVIEW',
    //                             422
    //                         );
    //                     }
    //                     $submission->markAsUnderReview($adminId);
    //                     break;

    //                 case 'approve':
    //                     $submission->approve($adminId);
    //                     break;

    //                 case 'reject':
    //                     if (!$reason || trim($reason) === '') {
    //                         throw new KycException(
    //                             'Rejection reason is required',
    //                             'REJECTION_REASON_REQUIRED',
    //                             422
    //                         );
    //                     }
    //                     $submission->reject($adminId, $reason);
    //                     break;
    //             }

    //             return $submission->fresh(['reviewer']);
    //         });
    //     } catch (Exception $e) {
    //         Log::error('Failed to review KYC submission', [
    //             'submission_id' => $id,
    //             'admin_id' => $adminId,
    //             'action' => $action,
    //             'error' => $e->getMessage()
    //         ]);
    //         throw $e;
    //     }
    // }

    //new 

    public function requestChanges(int $id, int $adminId, string $reason): KycSubmission
    {
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 500) {
            throw \Illuminate\Validation\ValidationException::withMessages(['reason' => 'Enter a reason between 1 and 500 characters.']);
        }

        return DB::transaction(function () use ($id, $adminId, $reason) {
            $submission = KycSubmission::query()->lockForUpdate()->findOrFail($id);
            $oldStatus = $submission->status;
            // Rejected submissions are editable by the existing upload clients.
            $submission->reject($adminId, $reason);
            $submission->messages()->create([
                'sender_type' => 'admin', 'sender_id' => $adminId,
                'message' => 'Changes requested: ' . $reason, 'is_read' => false,
            ]);
            app(KycOnboardingStepService::class)->syncAfterReview($submission, $adminId);
            foreach (\Modules\Restaurant\Models\Restaurant::where('admin_id', $submission->user_id)->get() as $restaurant) {
                app(\Modules\Restaurant\Services\OnboardingHistoryService::class)->record(
                    $restaurant->id, 'kyc', 'changes_requested', $oldStatus, 'rejected', $adminId,
                    ($submission->kycType?->name ?? 'KYC document') . ': ' . $reason
                );
            }
            DB::afterCommit(function () use ($submission, $reason) {
                app(\Modules\Restaurant\Services\OnboardingRefillNotificationService::class)->sendForDocument($submission, $reason);
            });

            return $submission;
        });
    }

    public function reviewSubmission(int $id, int $adminId, string $action, string $reason = null): KycSubmission
    {
        if ($action === 'request_changes') {
            return $this->requestChanges($id, $adminId, $reason ?? '');
        }
        try {
            $this->logKycOperation('Reviewing KYC submission', [
                'submission_id' => $id,
                'admin_id' => $adminId,
                'action' => $action
            ]);
            $submission = KycSubmission::find($id);
            if (!$submission) {
                throw KycException::submissionNotFound($id);
            }
            // validate submission can be reviewed
            $startingRefillReview = $action === 'under_review' && $submission->refill_requested;
            if (!in_array($submission->status, ['pending', 'under_review']) && !$startingRefillReview) {
                throw new KycException(
                    "Submission cannot be reviewed in '{$submission->status}' status",
                    'INVALID_SUBMISSION_STATUS',
                    422,
                    ['current_status' => $submission->status, 'allowed_statuses' => ['pending', 'under_review']]
                );
            }
            $validActions = ['under_review', 'approve', 'reject'];
            if (!in_array($action, $validActions)) {
                throw KycException::invalidAction($action);
            }
            return DB::transaction(function () use ($submission, $adminId, $action, $reason, $startingRefillReview) {
                switch ($action) {
                    case 'under_review':
                        if ($submission->status === 'under_review') {
                            throw new KycException(
                                'Submission is already under review',
                                'ALREADY_UNDER_REVIEW',
                                422
                            );
                        }
                        $submission->markAsUnderReview($adminId);
                        if ($startingRefillReview) {
                            $submission->messages()->create([
                                'sender_type' => 'admin', 'sender_id' => $adminId,
                                'message' => 'Refill request moved to review.', 'is_read' => false,
                            ]);
                        }
                        $this->sendKycNotification($submission, 'under_review');
                        break;
                    case 'approve':
                        // update submission
                        $submission->approve($adminId);
                        $this->sendKycNotification($submission, 'approved');

                        break;
                    case 'reject':
                        if (!$reason || trim($reason) === '') {
                            throw new KycException(
                                'Rejection reason is required',
                                'REJECTION_REASON_REQUIRED',
                                422
                            );
                        }

                        // Update submission
                        $submission->reject($adminId, $reason);
                        $this->sendKycNotification($submission, 'rejected', $reason);

                        break;
                }

                app(KycOnboardingStepService::class)->syncAfterReview($submission, $adminId);

                return $submission->fresh(['reviewer', 'owner']);
            });
        } catch (\Exception $e) {
            ApiExceptionHandler::handle($e);
            Log::error('Failed to review KYC submission', [
                'submission_id' => $id,
                'admin_id' => $adminId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    /**
     * Get submission statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = KycSubmission::query();

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['kyc_type_id'])) {
            $query->where('kyc_type_id', $filters['kyc_type_id']);
        }

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->pending()->count(),
            'under_review' => (clone $query)->underReview()->count(),
            'approved' => (clone $query)->approved()->count(),
            'rejected' => (clone $query)->rejected()->count()
        ];
    }

    /**
     * Get restaurant owner's document status badge HTML
     */
    public function getOwnerDocumentStatusBadge(int $ownerId): array
    {
        // Onboarding badge reflects onboarding (kyc) documents only, not compliance
        $submissions = KycSubmission::where('user_id', $ownerId)
            ->whereHas('kycType', fn ($q) => $q->kyc())
            ->get();

        $user = Admin::find($ownerId);

        if ($submissions->isEmpty()) {
            if ($user && $user->onboarding_outcome === 'rejected') {
                return [
                    'status' => 'rejected',
                    'label' => 'Rejected',
                    'class' => 'bg-red-100 text-red-800',
                    'pending_count' => 0
                ];
            }

            return [
                'status' => 'not_uploaded',
                'label' => 'Not Uploaded',
                'class' => 'bg-gray-100 text-gray-600',
                'pending_count' => 0
            ];
        }

        $hasRejected = $submissions->where('status', 'rejected')->count() > 0 || ($user && $user->onboarding_outcome === 'rejected');
        $hasUnderReview = $submissions->where('status', 'under_review')->count() > 0;
        $hasPending = $submissions->where('status', 'pending')->count() > 0;
        $approvedCount = $submissions->where('status', 'approved')->count();
        $activeKycTypesCount = \Modules\KYC\Models\KycType::where('is_active', 1)->kyc()->count();
        $pendingCount = $submissions->whereIn('status', ['pending', 'under_review'])->count();

        if ($hasRejected) {
            return [
                'status' => 'rejected',
                'label' => 'Rejected',
                'class' => 'bg-red-100 text-red-800',
                'pending_count' => $pendingCount
            ];
        }

        if ($approvedCount === $activeKycTypesCount && $approvedCount > 0) {
            return [
                'status' => 'all_approved',
                'label' => 'All Approved',
                'class' => 'bg-green-100 text-green-800',
                'pending_count' => 0
            ];
        }

        if ($hasUnderReview) {
            return [
                'status' => 'under_review',
                'label' => 'Under Review',
                'class' => 'bg-blue-100 text-blue-800',
                'pending_count' => $pendingCount
            ];
        }

        if ($hasPending) {
            return [
                'status' => 'pending_review',
                'label' => 'Pending Review',
                'class' => 'bg-yellow-100 text-yellow-800',
                'pending_count' => $pendingCount
            ];
        }

        return [
            'status' => 'unknown',
            'label' => 'Unknown',
            'class' => 'bg-gray-100 text-gray-600',
            'pending_count' => 0
        ];
    }

    // hasUploadedDocuments

    public function hasUploadedDocuments(int $userId): bool
    {
        return KycSubmission::where('user_id', $userId)->exists();
    }

    public function getUserSubmissions(int $userId)
    {
        return KycSubmission::where('user_id', $userId)
            ->with(['kycType', 'files', 'data'])
            ->get();
    }
    public function getActiveTypesCount(): int
    {
        return KycType::where('is_active', 1)->kyc()->count();
    }
    public function getActiveTypeById(int $typeId)
    {
        return KycType::where('is_active', 1)
            ->where('id', $typeId)
            ->with('fields')
            ->first();
    }
    public function getUserApprovedDocsCount(int $userId): int
    {
        return KycSubmission::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();
    }
    public function getPendingDocsCount(int $userId): int
    {
        return KycSubmission::where('user_id', $userId)
            ->whereIn('status', ['pending', 'under_review'])
            ->count();
    }
    public function getRejectedDocsCount(int $userId): int
    {
        return KycSubmission::where('user_id', $userId)
            ->where('status', 'rejected')
            ->count();
    }
    public function getUserDocumentStats(int $userId): array
    {
        // Onboarding gating excludes compliance types (uploaded separately)
        $submissions = KycSubmission::where('user_id', $userId)
            ->whereHas('kycType', fn ($q) => $q->kyc())
            ->get();
        $activeKycTypesCount = KycType::where('is_active', 1)->kyc()->count();

        return [
            'submissions' => $submissions,
            'active_types_count' => $activeKycTypesCount,
            'approved_count' => $submissions->where('status', 'approved')->count(),
            'pending_count' => $submissions->whereIn('status', ['pending', 'under_review'])->count(),
            'rejected_count' => $submissions->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Send KYC status email notification to the restaurant owner only.
     */
    protected function sendKycNotification(KycSubmission $submission, string $status, string $reason = null): void
    {
        $owner = $submission->owner;
        if (!$owner) {
            return;
        }

        $notificationService = app(\Modules\Notifications\Services\NotificationService::class);
        $kycTypeName = $submission->kycType->name ?? 'Document';

        $statusMessages = [
            'under_review' => 'Your document is currently under review.',
            'approved' => 'Your document has been approved.',
            'rejected' => 'Your document has been rejected.',
            'changes_requested' => 'Please refill or reupload this document for verification. Review the reason below.',
        ];

        $data = [
            'owner_name' => $owner->name,
            'owner_name' => $owner->name,
            'kyc_type_name' => $kycTypeName,
            'document_type' => $kycTypeName,
            'status' => $status,
            'status_message' => $statusMessages[$status] ?? '',
            'rejection_reason' => $reason,
            'reviewed_at' => now()->format('Y-m-d H:i:s'),
            'company_name' => config('app.name'),
            'current_year' => date('Y')
        ];

        try {
            $notificationService->trigger('KYC', 'KycStatus', $owner, $data);
            Log::info('KYC status notification sent to restaurant_owner', ['submission_id' => $submission->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send KYC notification to restaurant_owner', ['error' => $e->getMessage()]);
        }
    }
}
