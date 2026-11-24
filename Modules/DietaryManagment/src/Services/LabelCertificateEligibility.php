<?php

namespace Modules\DietaryManagment\Services;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Modules\DietaryManagment\Models\DietaryLabel;
use Modules\DietaryManagment\Models\RestaurantCertification;

class LabelCertificateEligibility
{
    public function forRestaurant(?int $restaurantId): array
    {
        $certificates = $restaurantId ? RestaurantCertification::where('restaurant_id', $restaurantId)
            ->get()->keyBy('certification_type_id') : collect();
        return DietaryLabel::active()->with('certificationType')->get()->mapWithKeys(function ($label) use ($certificates, $restaurantId) {
            $certificate = $label->certificationType ? $certificates->get($label->certificationType->id) : null;
            $required = $label->is_certification_required;
            $status = $certificate?->status ?? 'not_submitted';
            $uploaded = !empty($certificate?->file_path) && Storage::disk('local')->exists($certificate->file_path);
            $approved = $status === 'approved';
            $typeActive = (bool) $label->certificationType?->is_active;
            $allowed = !$required || ($typeActive && $approved && $uploaded);
            $message = !$required ? '' : match (true) {
                !$restaurantId => 'Choose a restaurant first.',
                $allowed => 'Certificate approved.',
                !$uploaded => 'Upload certificate for admin approval.',
                !$typeActive => 'Certification unavailable. Contact admin.',
                $status === 'rejected' => 'Certificate rejected. Please resubmit.',
                $status === 'changes_requested' => 'Update and resubmit certificate.',
                in_array($status, ['submitted', 'under_review'], true) => 'Certificate awaiting admin approval.',
                default => 'Upload certificate for admin approval.',
            };
            return [(string) $label->id => ['allowed' => $allowed, 'required' => $required,
                'is_certification_required' => $required, 'is_uploaded' => $uploaded, 'is_approved' => $approved,
                'certification_type_id' => $label->certificationType?->id, 'certificate_id' => $certificate?->id,
                'status' => $status, 'message' => $message, 'name' => $label->name]];
        })->all();
    }

    public function assertAllowed(?int $restaurantId, array $labelIds): void
    {
        $eligibility = $this->forRestaurant($restaurantId);
        $errors = [];
        foreach ($labelIds as $id) {
            $label = $eligibility[(string) $id] ?? null;
            if (!$label || !$label['allowed']) {
                $errors[] = $label
                    ? $label['name'].': '.$label['message']
                    : 'Choose an active dietary label.';
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages(['dietary_label_ids' => $errors]);
        }
    }
}
