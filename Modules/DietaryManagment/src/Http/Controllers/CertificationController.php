<?php

namespace Modules\DietaryManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DietaryManagment\DataView\CertificationGrid;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\DietaryManagment\Models\RestaurantCertification;
use Modules\DietaryManagment\Services\OnboardingCertificateService;
use Modules\DietaryManagment\Services\CertificationAccess;

class CertificationController extends Controller
{
    public function index()
    {
        $lists = fn_datagrid(CertificationGrid::class)->process();

        return view('dietarymanagment::certifications.index', compact('lists'));
    }

    public function show(Request $request, int $id)
    {
        $admin = $request->user('admin');
        abort_unless($admin && $admin->status && $admin->hasPermission('admin.dietarymanagment.certifications.show'), 403);
        $access = app(CertificationAccess::class);
        $ownerSide = $access->isOwner($admin);
        $query = RestaurantCertification::with(['restaurant', 'certificationType.dietaryLabel', 'reviewer']);
        $certificate = $access->scope($query, $admin)->findOrFail($id);
        return view('dietarymanagment::certifications.show', compact('certificate', 'ownerSide'));
    }

    public function review(Request $request, int $id, OnboardingCertificateService $service)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['under_review', 'approved', 'rejected', 'changes_requested'])],
            'admin_notes' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected,changes_requested'],
            'document_version' => ['required', 'string', 'size:64'], 'return_to_onboarding' => ['sometimes', 'boolean'],
        ]);
        $certificate = $service->review($request->user('admin'), $id, $data['status'], $data['admin_notes'] ?? null, $data['document_version']);
        if ($request->boolean('return_to_onboarding')) {
            return redirect()->route('admin.restaurant.approvals.show', ['id' => $certificate->restaurant_id, 'step' => 'dietary_certificates'])->with('success', 'Certificate review saved.');
        }
        return redirect()->route('admin.dietarymanagment.certifications.show', $id)->with('success', 'Certificate review saved.');
    }
}
