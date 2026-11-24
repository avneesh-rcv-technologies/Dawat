<?php

namespace Modules\Admin\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = Auth::guard(\Illuminate\Support\Facades\Auth::getDefaultDriver())->user();
        if (app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($user)) {
            if ($request->query('tab') === 'dietary_certificates') {
                return redirect()->route('settings.restaurant.dietary-certificates.index', $request->only('restaurant_id'));
            }

            return view('admin::pages.profile.edit', [
                'user' => $user, 'certificateRestaurants' => collect(),
                'certificateRestaurant' => null, 'certificateData' => [],
            ]);
        }
        $certificateRestaurants = \Modules\Restaurant\Models\Restaurant::where('admin_id', $user->id)->orderBy('name')->get();
        $certificateRestaurant = $request->filled('restaurant_id')
            ? $certificateRestaurants->firstWhere('id', (int) $request->input('restaurant_id')) : $certificateRestaurants->first();
        if ($request->filled('restaurant_id') && !$certificateRestaurant) abort(404);
        $certificateData = $certificateRestaurant
            ? app(\Modules\DietaryManagment\Services\OnboardingCertificateService::class)->screen($certificateRestaurant) : [];
        return view('admin::pages.profile.edit', compact('user', 'certificateRestaurants', 'certificateRestaurant', 'certificateData'));
    }


    public function uploadCertificate(Request $request, \Modules\DietaryManagment\Services\OnboardingCertificateService $service)
    {
        abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($request->user('admin')), 403);
        $data = $request->validate([
            'restaurant_id' => ['required', 'integer'],
            'dietary_label_id' => ['required', 'string', 'max:36'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        $service->upload($request->user('admin'), $data['restaurant_id'], $data['dietary_label_id'], $request->file('document'));
        return redirect()->route('profile.edit', ['tab' => 'dietary_certificates', 'restaurant_id' => $data['restaurant_id']])
            ->with('success', 'Certificate submitted. You can use this dietary label after admin approval.');
    }

    public function update(Request $request)
    {
        $user = Auth::guard(\Illuminate\Support\Facades\Auth::getDefaultDriver())->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:60',
            'current_password' => ['bail', 'required_with:password,password_confirmation', 'nullable', 'string', 'current_password:'.Auth::getDefaultDriver()],
            'password' => ['required_with:current_password,password_confirmation', 'nullable', 'string', 'confirmed', 'different:current_password', new StrongPassword()],
            'image' => ['bail', 'nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'current_password.required_with' => 'Please enter your old password.',
            'current_password.current_password' => 'The old password is incorrect.',
            'password.different' => 'The new password must be different from your old password.',
            'image.file' => 'Please choose a valid JPG, PNG, or GIF profile photo.',
            'image.image' => 'The profile photo must be a valid JPG, PNG, or GIF image. Other formats are not supported.',
            'image.mimes' => 'The profile photo must be a JPG, PNG, or GIF image.',
            'image.max' => 'The profile photo must not exceed 2 MB.',
            'image.uploaded' => 'The profile photo could not be uploaded. Choose a JPG, PNG, or GIF image no larger than 2 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }


        $user->name = $request->name;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->setRememberToken(\Illuminate\Support\Str::random(60));
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            fn_upload_image($request->file('image'), 'admin_avatar', $user->id, true, 'image');
        }
        $user->save();

        if ($request->filled('password')) {
            $loginRoute = app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($user)
                ? 'website.partner-login' : 'admin.login.form';
            app(\Modules\Acl\Services\AdminSessionRevoker::class)->revoke($user);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully. Please sign in again.',
                'redirect_url' => route($loginRoute),
            ]);
        }

        $message = 'Account details updated successfully.';
        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect_url' => route('profile.edit')
        ]);
    }
}
