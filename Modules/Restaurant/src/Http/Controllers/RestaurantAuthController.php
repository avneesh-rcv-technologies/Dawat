<?php

namespace Modules\Restaurant\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Http\Controllers\AdminAuthController;
use Modules\Acl\Models\Admin;
use Modules\Acl\Services\AdminEmailVerificationService;
use Modules\Acl\Services\AdminPasswordResetService;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Services\RestaurantOnboardingAccessService;

class RestaurantAuthController extends AdminAuthController
{
    public function __construct()
    {
        // Routes supply middleware; share the existing admin guard and password broker.
    }

    protected function findAdmin(string $email): ?Admin
    {
        return Admin::with('role')->where('email', $email)->first();
    }

    protected function findAdminById(string $id): ?Admin
    {
        return Admin::with('role')->find($id);
    }

    protected function restaurantFor(Admin $admin): ?Restaurant
    {
        return Restaurant::where('admin_id', $admin->id)->first();
    }

    protected function isOwner(?Admin $admin): bool
    {
        return app(RestaurantApprovalAccess::class)->isOwner($admin);
    }

    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
    }

    public function showLoginForm()
    {
        return view('website::pages.partner-signup', ['isLogin' => true, 'authMode' => 'login']);
    }

    public function login(Request $request)
    {
        $this->normalizeEmail($request);
        $captchaEnabled = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled();
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'g-recaptcha-response' => [
                \Illuminate\Validation\Rule::requiredIf($captchaEnabled),
                ...($captchaEnabled ? [new \Modules\Website\Rules\Recaptcha] : []),
            ],
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
        ]);
        $admin = $this->findAdmin($credentials['email']);
        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return response()->json(['success' => false, 'errors' => ['email' => ['Invalid email or password.']]], 422);
        }
        if (!$admin->role) {
            return response()->json(['success' => false, 'errors' => ['email' => ['Your account approval is pending or your account is inactive. Please contact the administrator.']]], 403);
        }
        if (!$this->isOwner($admin)) {
            return response()->json(['success' => false, 'errors' => ['email' => ['Invalid email or password.']]], 422);
        }
        if (!$admin->status) {
            return response()->json(['success' => false, 'errors' => ['email' => ['Your account is not active yet. Please contact the administrator.']]], 403);
        }
        if (!$admin->email_verified_at) {
            try {
                app(AdminEmailVerificationService::class)->send($admin, 'website.partner.verification.verify');
            } catch (ValidationException $exception) {
                return response()->json(['success' => false, 'verification_required' => true, 'errors' => $exception->errors()], 422);
            } catch (\Throwable $exception) {
                \Illuminate\Support\Facades\Log::warning('Partner verification email could not be sent.', ['admin_id' => $admin->id, 'exception_type' => get_class($exception)]);
                return response()->json(['success' => false, 'errors' => ['email' => ['Unable to send the verification email. Please try again later or contact the administrator.']]], 503);
            }
            return response()->json([
                'success' => false, 'verification_required' => true,
                'message' => 'A verification link has been sent to your email. Open it to verify your email, then log in again.',
            ]);
        }

        $onboarding = app(RestaurantOnboardingAccessService::class)->forAdmin($admin);
        $restaurant = $this->restaurantFor($admin);
        if ($restaurant && $restaurant->status !== 'active' && !$onboarding) {
            $message = $restaurant->approval_status === 'pending'
                ? 'Your restaurant account is pending admin approval. Please wait for the administrator to approve your account.'
                : 'Your restaurant is not active yet. Please contact the administrator.';
            return response()->json(['success' => false, 'errors' => ['email' => [$message]]], 403);
        }
        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        return response()->json([
            'success' => true, 'message' => 'Login successful.',
            'redirect_url' => $onboarding ? route('restaurant.onboarding.kyc') : route('dashboard.index'),
        ]);
    }

    public function verifyEmail(Request $request, string $id, string $hash)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('website.partner-login')->with('error', 'This verification link is invalid or has expired. Log in again to request a new link.');
        }
        $admin = $this->findAdminById($id);
        if (!$this->isOwner($admin) || !hash_equals(sha1($admin->email), $hash)) {
            return redirect()->route('website.partner-login')->with('error', 'This verification link is no longer valid. Please log in to request a new link.');
        }
        if (!$admin->email_verified_at) {
            $admin->forceFill(['email_verified_at' => now()])->save();
            event(new Verified($admin));
        }
        return redirect()->route('website.partner-login')->with('success', 'Your email has been verified. You can now log in.');
    }

    public function showForgotPasswordForm()
    {
        return view('website::pages.partner-signup', ['isLogin' => true, 'authMode' => 'forgot']);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->normalizeEmail($request);
        $captchaEnabled = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled();
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'g-recaptcha-response' => [
                \Illuminate\Validation\Rule::requiredIf($captchaEnabled),
                ...($captchaEnabled ? [new \Modules\Website\Rules\Recaptcha] : []),
            ],
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
        ]);
        $admin = $this->findAdmin($request->input('email'));
        $message = 'Please check your inbox for the password reset link.';
        if (!$this->isOwner($admin)) {
            return response()->json([
                'success' => false,
                'errors' => ['email' => ['No restaurant owner account was found with this email. Please sign up first.']],
            ], 422);
        }
        try {
            $status = $this->broker()->sendResetLink($request->only('email'), function ($owner, $token) {
                if (!$this->isOwner($owner)) {
                    throw ValidationException::withMessages(['email' => ['Unable to send a password reset link for this account.']]);
                }
                try {
                    app(AdminPasswordResetService::class)->send($owner, $token, 'website.partner.password.reset');
                } catch (\Throwable $exception) {
                    $this->broker()->deleteToken($owner);
                    throw $exception;
                }
            });
        } catch (ValidationException $exception) {
            return response()->json(['success' => false, 'errors' => $exception->errors()], 422);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['success' => false, 'message' => 'Unable to send the reset email. Please try again later.'], 503);
        }
        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json(['success' => false, 'errors' => ['email' => [
                $status === Password::RESET_THROTTLED
                    ? 'A reset link was recently requested. Please wait 60 seconds before requesting another.'
                    : 'Unable to send a reset link. Please try again.',
            ]]], $status === Password::RESET_THROTTLED ? 429 : 422);
        }
        return response()->json(['success' => true, 'message' => $message]);
    }

    public function showResetPasswordForm(Request $request)
    {
        $token = $request->route('token');
        $email = $request->query('email');
        $email = is_string($email) ? strtolower(trim($email)) : '';
        $admin = strlen($email) <= 255 && filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->findAdmin($email) : null;
        $valid = $this->isOwner($admin) && is_string($token) && strlen($token) <= 255 && $this->broker()->tokenExists($admin, $token);

        return view('website::pages.partner-signup', [
            'isLogin' => true, 'authMode' => 'reset', 'email' => $email,
            'token' => is_string($token) ? $token : '', 'linkValid' => (bool) $valid,
        ]);
    }

    public function reset(Request $request)
    {
        $this->normalizeEmail($request);
        $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        if (!$this->isOwner($this->findAdmin($request->input('email')))) {
            return response()->json(['success' => false, 'errors' => ['email' => ['This reset link is invalid or has expired. Please request a new link.']]], 422);
        }
        $response = parent::reset($request);
        $data = $response->getData(true);
        if ($data['success'] ?? false) {
            $response->setData(array_replace($data, ['redirect_url' => route('website.partner-login')]));
        }
        return $response;
    }

    protected function resetPassword($user, $password)
    {
        // Recheck the role on the exact user resolved by the password broker.
        if (!$this->isOwner($user)) {
            throw ValidationException::withMessages(['email' => ['This reset link is invalid or has expired. Please request a new link.']]);
        }
        parent::resetPassword($user, $password);
    }
}
