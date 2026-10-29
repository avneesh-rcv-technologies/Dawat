<?php

namespace Modules\Restaurant\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Modules\KYC\Models\KycType;
use Modules\KYC\Services\KycSubmissionService;
use Modules\KYC\Models\KycSubmission;
use Modules\Restaurant\Services\OnboardingKycReportService;
use Modules\Restaurant\Services\RestaurantService;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Illuminate\Support\Facades\Log;

class OnboardingDocumentController extends Controller
{
    public function __construct(
        protected KycSubmissionService $submissionService,
        protected OnboardingKycReportService $kycReportService,
        protected RestaurantService $restaurantService
    ) {
    }

    public function getDocuments(): JsonResponse
    {
        $admin = request()->user();
        abort_unless($admin instanceof \Modules\Acl\Models\Admin, 403);
        $types = KycType::active()->kyc()->with('fields')->orderBy('id')->get();

        // Attach current submission info for each type for this admin
        $submissions = KycSubmission::where('user_id', $admin->id)->with(['data', 'files', 'messages'])->latest('id')->get()->unique('kyc_type_id')->keyBy('kyc_type_id');

        $payload = $types->map(function ($type) use ($submissions) {
            $submission = $submissions->get($type->id);
            return [
                'kyc_type' => $type,
                'current_submission' => $submission ? [
                    'id' => $submission->id,
                    'status' => $submission->status,
                    'refill_requested' => $submission->refill_requested,
                    'submitted_at' => $submission->created_at?->toDateTimeString(),
                    'rejection_reason' => $submission->rejection_reason,
                    'values' => $submission->data->pluck('field_value', 'field_key'),
                    'files' => $submission->files->map(fn ($file) => [
                        'field_id' => $file->field_id,
                        'name' => $file->original_name ?: basename($file->file_path),
                        'url' => $file->url,
                        'is_image' => in_array($file->file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/avif'], true),
                    ]),
                ] : null,
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $payload,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function getKycReport(): JsonResponse
    {
        $admin = request()->user();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }
        abort_unless($admin instanceof \Modules\Acl\Models\Admin, 403);

        try {
            $data = $this->kycReportService->getReport($admin);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to generate onboarding KYC report', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate KYC report.',
            ], 500);
        }
    }


    //  get Kyc report Details of User with uploaded document messgafe each document submission status and also final status of user onboarding

    public function uploadDocuments(Request $request): JsonResponse
    {
        return $this->saveDocuments($request, false);
    }

    public function uploadRejectedDocuments(Request $request): JsonResponse
    {
        return $this->saveDocuments($request, true);
    }

    private function saveDocuments(Request $request, bool $reupload): JsonResponse
    {
        $admin = $request->user();
        abort_unless($admin instanceof \Modules\Acl\Models\Admin, 403);
        $validator = Validator::make($request->all(), [
            'documents' => 'required|array|min:1',
            'documents.*.kyc_type_id' => 'required|integer|distinct|exists:kyc_types,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $documents = array_replace_recursive($request->input('documents', []), $request->file('documents', []));
        try {
            $created = DB::transaction(function () use ($admin, $documents, $reupload) {
                $ids = array_column($documents, 'kyc_type_id');
                $types = KycType::active()->kyc()->with('fields')->whereIn('id', $ids)->get()->keyBy('id');
                $existing = KycSubmission::where('user_id', $admin->id)->whereIn('kyc_type_id', $ids)
                    ->with(['data', 'files'])->lockForUpdate()->get();

                // Check the entire batch before replacing or storing any documents.
                foreach ($documents as $index => $document) {
                    $type = $types->get($document['kyc_type_id']);
                    $submissions = $existing->where('kyc_type_id', $document['kyc_type_id']);
                    $error = match (true) {
                        !$type => 'This KYC document type is inactive or unavailable.',
                        $submissions->contains('status', 'approved') => 'Approved documents cannot be reuploaded.',
                        $reupload && $submissions->isEmpty() => 'This document has not been submitted yet. Use the upload endpoint.',
                        !$reupload && $submissions->isNotEmpty() => 'This document has already been submitted. Use the reupload endpoint.',
                        default => null,
                    };
                    if ($error) {
                        throw \Illuminate\Validation\ValidationException::withMessages(["documents.{$index}.kyc_type_id" => $error]);
                    }
                    try {
                        $this->submissionService->validateFieldData($type, $document);
                    } catch (\Modules\KYC\Exceptions\KycException $exception) {
                        $errors = $exception->getContext()['validation_errors'] ?? [];
                        $mapped = [];
                        foreach ($errors as $field => $messages) {
                            $mapped["documents.{$index}.{$field}"] = $messages;
                        }
                        throw \Illuminate\Validation\ValidationException::withMessages(
                            $mapped ?: ["documents.{$index}" => $exception->getMessage()]
                        );
                    }
                    if ($reupload && !\Modules\Restaurant\Services\OnboardingChanges::documentChanged(
                        $type, $submissions->sortByDesc('id')->first(), $document
                    )) {
                        \Modules\Restaurant\Services\OnboardingChanges::reject("documents.{$index}");
                    }
                }

                $created = [];
                foreach ($documents as $document) {
                    $typeId = (int) $document['kyc_type_id'];
                    unset($document['kyc_type_id']);
                    $created[] = $this->submissionService->createSubmission($admin->id, $typeId, $document, $reupload)
                        ->fresh(['data', 'files']);
                }
                app(\Modules\Restaurant\Services\KycOnboardingStepService::class)->syncAfterReview(end($created), 0, true);
                foreach (Restaurant::where('admin_id', $admin->id)->lockForUpdate()->get() as $restaurant) {
                    $restaurant->update(['onboarding_step' => OnboardingStep::KYC->order()]);
                }
                if (!$reupload) {
                    DB::afterCommit(function () use ($admin, $types, $created) {
                        try {
                            app(\Modules\Notifications\Services\NotificationService::class)->trigger('KYC', 'KycDocumentSubmission', $admin, [
                                'title' => 'KYC Documents Submitted', 'owner_name' => $admin->name,
                                'document_types' => $types->pluck('name')->all(), 'submission_count' => count($created),
                                'submission_url' => route('restaurant.onboarding.kyc', ['step' => 'kyc']),
                                'request_message' => 'Your KYC documents have been submitted successfully. Our team will review them shortly.',
                                'company_name' => config('app.name'), 'current_year' => now()->year,
                                'data' => ['submission_ids' => collect($created)->pluck('id')->all()],
                            ]);
                        } catch (\Throwable $exception) {
                            Log::warning('Unable to send KYC submission notification.', ['admin_id' => $admin->id]);
                        }
                    });
                }
                return $created;
            });

            $message = $reupload ? 'Documents reuploaded successfully.' : 'Documents uploaded successfully.';
            $response = response()->json(['success' => true, 'message' => $message, 'data' => $created]);
            if ($request->hasSession()) {
                $request->session()->flash('kyc_success', $message . ' They are now awaiting review.');
            }

            return $response;
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['success' => false, 'message' => 'Please check your documents.', 'errors' => $exception->errors()], 422);
        } catch (\Modules\KYC\Exceptions\KycException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage(),
                'errors' => $exception->getContext()['validation_errors'] ?? []], 422);
        } catch (\Throwable $exception) {
            Log::error('Unable to save onboarding documents.', ['admin_id' => $admin->id, 'error' => $exception->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Unable to save documents. Please try again.'], 500);
        }
    }

    /**
     * Return list of rejected KYC document types for the authenticated admin
     */
    public function getRejectedDocuments(): JsonResponse
    {
        $admin = request()->user();
        abort_unless($admin instanceof \Modules\Acl\Models\Admin, 403);

        $rejected = KycSubmission::where('user_id', $admin->id)
            ->where('status', 'rejected')
            ->with('kycType.fields')
            ->get()
            ->groupBy('kyc_type_id')
            ->map(function ($group) {
                $submission = $group->first();
                return [
                    'kyc_type_id' => $submission->kyc_type_id,
                    'kyc_type' => $submission->kycType,
                    'submission_id' => $submission->id,
                    'rejected_at' => $submission->updated_at?->toDateTimeString(),
                    'rejection_reason' => $submission->rejection_reason ?? null,
                ];
            })->values();

        return response()->json([
            'success' => true,
            'data' => $rejected,
        ]);
    }
}
