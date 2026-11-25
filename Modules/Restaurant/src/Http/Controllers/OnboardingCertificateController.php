<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\DietaryManagment\Models\RestaurantCertification;
use Modules\DietaryManagment\Services\OnboardingCertificateService;

class OnboardingCertificateController
{
    public function store(Request $request, OnboardingCertificateService $service)
    {
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer'], 'dietary_label_id' => ['required', 'string', 'max:36'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        $service->upload($request->user('admin'), $data['restaurant_id'], $data['dietary_label_id'], $request->file('document'));
        return redirect()->route('restaurant.onboarding.kyc', ['step' => 'dietary_certificates'])
            ->with('success', 'Certificate submitted for review. All uploaded dietary certificates must be approved before you can continue.');
    }

    public function skip(Request $request, OnboardingCertificateService $service)
    {
        $data = $request->validate(['restaurant_id' => ['required', 'integer']]);
        $service->skip($request->user('admin'), $data['restaurant_id']);
        return redirect()->route('restaurant.onboarding.kyc', ['step' => 'kyc'])->with('success', 'Continue with KYC. You can upload dietary certificates later.');
    }

    public function document(Request $request, int $id)
    {
        $certificate = RestaurantCertification::with('restaurant')->findOrFail($id);
        $admin = $request->user('admin');
        $owner = (int) $certificate->restaurant->admin_id === (int) $admin->id;
        $reviewer = ($admin->hasPermission('restaurant.onboarding.certificates.document')
                || $admin->hasPermission('admin.dietarymanagment.certifications.show'))
            && !app(\Modules\DietaryManagment\Services\CertificationAccess::class)->isOwner($admin);
        abort_unless($admin->status && (($owner && $admin->email_verified_at) || $reviewer), 403);
        abort_unless($certificate->file_path && Storage::disk('local')->exists($certificate->file_path), 404);
        if ($request->boolean('preview')) {
            $mime = Storage::disk('local')->mimeType($certificate->file_path);
            abort_unless(in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true), 415);
            if (str_starts_with($mime, 'image/')) {
                return response()->view('restaurant::onboarding.certificate-image-preview', [
                    'image' => 'data:'.$mime.';base64,'.base64_encode(Storage::disk('local')->get($certificate->file_path)),
                    'fileName' => $certificate->file_name ?: 'Certificate image',
                ])->header('X-Content-Type-Options', 'nosniff')
                    ->header('Cache-Control', 'private, no-store')
                    ->header('Content-Security-Policy', "default-src 'none'; img-src data:; style-src 'unsafe-inline'; sandbox");
            }
            return Storage::disk('local')->response($certificate->file_path, $certificate->file_name ?: 'certificate', [
                'Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
                'Content-Security-Policy' => "sandbox; default-src 'none'",
            ], 'inline');
        }
        return Storage::disk('local')->download($certificate->file_path, $certificate->file_name ?: 'certificate',
            ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
