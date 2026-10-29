<?php

namespace Modules\KYC\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\KYC\Models\ComplianceRecord;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycType;
use Modules\KYC\Services\ComplianceService;
use Modules\KYC\Services\KycSubmissionService;
use Modules\Notifications\Services\NotificationService;
use Throwable;

class ComplianceApiController extends Controller
{
    public function __construct(
        protected ComplianceService $compliance,
        protected KycSubmissionService $submissions,
        protected NotificationService $notificationService,
    ) {}

    /**
     * GET api/owner/compliance/status
     */
    public function status(Request $request)
    {
        $owner = Auth::guard('owner_api')->user();
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $types = KycType::compliance()->where('is_active', 1)->with('fields')->get();
        $typeIds = $types->pluck('id');

        $records = ComplianceRecord::where('user_id', $owner->id)
            ->whereIn('kyc_type_id', $typeIds)
            ->get()
            ->keyBy('kyc_type_id');

        $submissions = KycSubmission::where('user_id', $owner->id)
            ->whereIn('kyc_type_id', $typeIds)
            ->with(['data', 'files'])
            ->latest('id')
            ->get()
            ->unique('kyc_type_id')
            ->keyBy('kyc_type_id');

        $data = $types->map(function ($type) use ($records, $submissions) {
            $record     = $records->get($type->id);
            $submission = $submissions->get($type->id);

            $isExpired = $record ? $record->isExpired() : false;
            $canUpload = !$submission
                || in_array($submission->status, ['rejected'], true)
                || $isExpired
                || ($record && $record->status === 'expired');

            $status = $submission->status ?? 'not_uploaded';

            $dataByFieldId  = $submission ? $submission->data->keyBy('field_id') : collect();
            $filesByFieldId = $submission ? $submission->files->keyBy('field_id') : collect();

            $fields = $type->fields->map(function ($f) use ($dataByFieldId, $filesByFieldId) {
                $isFile   = method_exists($f, 'isFileType') ? $f->isFileType() : in_array($f->field_type, ['file', 'image'], true);
                $dataRow  = $dataByFieldId->get($f->id);
                $fileRow  = $filesByFieldId->get($f->id);
                $value    = $dataRow->field_value ?? null;
                $isFilled = $isFile ? !empty($fileRow) : ($value !== null && $value !== '');

                return [
                    'id'          => $f->id,
                    'name'        => $f->field_name,
                    'key'         => $f->field_key,
                    'type'        => $f->field_type,
                    'is_required' => (bool) $f->is_required,
                    'is_filled'   => $isFilled,
                ];
            })->values();

            return [
                'id'             => $type->id,
                'name'           => $type->name,
                'status'         => $status,
                'can_upload'     => (bool) $canUpload,
                'expires_at'     => $record ? optional($record->expires_at)->toDateString() : null,
                'is_expired'     => (bool) $isExpired,
                'fields'         => $fields,
                'missing_fields' => $fields->filter(fn ($f) => $f['is_required'] && !$f['is_filled'])->pluck('key')->values(),
            ];
        })->values();

        return response()->json([
            'success'  => true,
            'pending'  => $data->whereIn('status', ['pending', 'under_review'])->count(),
            'approved' => $data->where('status', 'approved')->count(),
            'data'     => $data,
        ]);
    }

    /**
     * POST api/owner/compliance/upload
     * First upload OR renewal for one compliance type.
     */
    public function upload(Request $request)
    {
        $owner = Auth::guard('owner_api')->user();
        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $request->validate(['kyc_type_id' => 'required|integer']);
        $typeId = (int) $request->kyc_type_id;

        if (!$this->compliance->isComplianceType($typeId)) {
            return response()->json(['success' => false, 'message' => 'Not a compliance type'], 422);
        }

        $type = KycType::with('fields')->find($typeId);
        if (!$type || !$type->is_active) {
            return response()->json(['success' => false, 'message' => 'Compliance type unavailable'], 422);
        }

        // Build dynamic validation rules from the type's fields
        $rules    = ['kyc_type_id' => 'required|integer'];
        $messages = [];
        foreach ($type->fields as $field) {
            $rules[$field->field_key] = $field->getValidationRules();
            $messages = array_merge($messages, $field->getValidationMessages());
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Assemble field_key => value|UploadedFile (shape createSubmission expects)
        $data = [];
        foreach ($type->fields as $field) {
            $key = $field->field_key;
            $data[$key] = $field->isFileType() ? $request->file($key) : $request->input($key);
        }

        try {
            // createSubmission blocks only if a pending/under_review submission exists,
            // so an approved-but-expired one can be renewed with a fresh submission.
            $submission = $this->submissions->createSubmission($owner->id, $typeId, $data);
            // Re-upload after expiry: clear expires_at so is_expired becomes false
            $this->compliance->clearExpiryOnReupload($owner->id, $typeId, $submission->id);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        // Notify admins that a compliance document was submitted (reuse KYC submission pipeline).
        try {
            $this->notificationService->trigger(
                'KYC',
                'KycDocumentSubmission',
                $owner,
                [
                    'title' => 'Compliance document submitted',
                    'owner_name' => $owner->name,
                    'document_types' => [$type->name],
                    'submission_count' => 1,
                    'submission_url' => url('/restaurant/kyc/history'),
                    'request_message' => 'A compliance document was submitted and is pending review.',
                    'company_name' => config('app.name', 'Our Company'),
                    'current_year' => date('Y'),
                ]
            );
        } catch (Throwable $e) {
            // notification failure should not break the upload
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Compliance documents submitted for review.',
            'submission_id' => $submission->id,
        ]);
    }
}
