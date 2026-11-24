<?php

namespace Modules\Acl\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Notifications\Services\NotificationService;

class AdminAuthController extends Controller
{
    protected $guard = 'admin';
    protected $redirectTo = 'dashboard.index';



    public function __construct()
    {
        $this->middleware('guest:admin')->except(['logout']);
    }

   public function login(Request $request)
{
    $captchaEnabled = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled();
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
        'remember' => 'boolean',
        'remember-me' => 'boolean',
        'g-recaptcha-response' => [
            \Illuminate\Validation\Rule::requiredIf($captchaEnabled),
            ...($captchaEnabled ? [new \Modules\Website\Rules\Recaptcha] : []),
        ],
    ], [
        'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $credentials = $validator->validated();

    $remember = $request->boolean(
        'remember',
        $request->boolean('remember-me')
    );

    // Check credentials without creating a session.
    $admin = \Modules\Acl\Models\Admin::where(
        'email',
        $credentials['email']
    )->first();

    if (
        !$admin ||
        !Auth::guard($this->guard)->validate([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])
    ) {
        return response()->json([
            'success' => false,
            'errors' => [
                'email' => ['Invalid email or password'],
            ],
        ], 200);
    }

    // Restaurant partners must use their dedicated login; other roles retain access.
    if (app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($admin)) {
        return response()->json([
            'success' => false,
            'errors' => ['email' => ['Invalid email or password']],
        ], 403);
    }

    // 1. Check admin account status.
    if (!$admin->status) {
        return response()->json([
            'success' => false,
            'message' => 'Your account is inactive. Please contact the administrator.',
        ], 200);
    }

    // 2. The protected Admin role does not require email verification.
    $isAdmin = $admin->role?->isProtected() ?? false;
    if (!$isAdmin && empty($admin->email_verified_at)) {
        try {
            app(\Modules\Acl\Services\AdminEmailVerificationService::class)->send($admin);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'verification_required' => true,
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => false,
            'verification_required' => true,
            'message' => 'A verification link has been sent to your email. Please verify your email, then sign in again.',
        ], 200);
    }

    // 3. Check restaurant status if this admin has a restaurant.
    $restaurant = \Modules\Restaurant\Models\Restaurant::where(
        'admin_id',
        $admin->id
    )->first();

    $onboardingRestaurant = app(\Modules\Restaurant\Services\RestaurantOnboardingAccessService::class)
        ->forAdmin($admin);

    if ($restaurant && $restaurant->status !== 'active' && !$onboardingRestaurant) {
        return response()->json([
            'success' => false,
            'message' => 'Your restaurant is not active. Please contact the administrator.',
            'restaurant_status' => $restaurant->status,
        ], 200);
    }

    // 4. All checks passed. Create the authenticated session.
    if (
        Auth::guard($this->guard)->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)
    ) {
        $request->session()->regenerate();
        $request->session()->put('auth.last_activity_at', now()->timestamp);

        $admin = Auth::guard($this->guard)->user();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect_url' => $onboardingRestaurant ? route('restaurant.onboarding.kyc') : $this->redirectPath(),
            'user' => $admin,
        ]);
    }

    return response()->json([
        'success' => false,
        'errors' => [
            'email' => ['Invalid email or password'],
        ],
    ], 200);
}

    public function logout(Request $request)
    {
        $actor = Auth::guard($this->guard)->user();
        $redirects = app(\Modules\Acl\Services\LoginRedirector::class);
        $loginUrl = $redirects->url($request, $actor);

        Auth::guard($this->guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (!$request->expectsJson()) {
            $response = redirect()->to($loginUrl);
            return $actor ? $redirects->remember($response, $request, $actor) : $response;
        }

        $response = response()->json([
            'success' => true,
            'message' => 'Logout successful',
            'redirect_url' => $loginUrl,
        ]);
        return $actor ? $redirects->remember($response, $request, $actor) : $response;
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => ['required', 'string', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::guard($this->guard)->login($user);

        if (app(NotificationService::class)) {
            app(NotificationService::class)->trigger(
                'Admin',
                'Register',
                $user,
                ['user' => $user]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'redirect_url' => $this->redirectPath(),
            'user' => $user,
        ]);
    }

    public function showForgotPasswordForm()
    {
        return view('admin::auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }
        $captchaEnabled = app(\Modules\GeneralSetting\Services\CaptchaSettings::class)->enabled();
        $request->validate([
            'email' => 'bail|required|string|max:255|email',
            'g-recaptcha-response' => [
                \Illuminate\Validation\Rule::requiredIf($captchaEnabled),
                ...($captchaEnabled ? [new \Modules\Website\Rules\Recaptcha] : []),
            ],
        ], [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA challenge.',
        ]);

        try {
            $status = $this->broker()->sendResetLink($request->only('email'), function ($admin, $token) {
                try {
                    app(\Modules\Acl\Services\AdminPasswordResetService::class)->send($admin, $token);
                } catch (\Throwable $exception) {
                    $this->broker()->deleteToken($admin);
                    throw $exception;
                }
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            return response()->json(['success' => false, 'errors' => $exception->errors()], 422);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'success' => false,
                'errors' => ['email' => ['Unable to send the reset email. Please try again later.']],
            ], 422);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => false,
                'errors' => ['email' => [$status === Password::RESET_THROTTLED
                    ? 'A reset link was recently requested. Please wait 60 seconds before requesting another.'
                    : 'We cannot find an account with that email address.']],
            ], $status === Password::RESET_THROTTLED ? 429 : 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your password reset email has been queued. Please check your inbox shortly.',
            'redirect_url' => route('admin.login.form'),
        ]);
    }


    public function showResetPasswordForm(Request $request)
    {
        $token = $request->route('token');
        $email = $request->query('email');
        $validEmail = is_string($email) && strlen($email) <= 255 && filter_var($email, FILTER_VALIDATE_EMAIL);
        $admin = $validEmail ? $this->broker()->getUser(['email' => $email]) : null;
        $linkValid = $admin && is_string($token) && strlen($token) <= 255
            && $this->broker()->tokenExists($admin, $token);

        return view('admin::auth.passwords.reset', [
            'token' => is_string($token) ? $token : '',
            'email' => $validEmail ? $email : '',
            'linkValid' => (bool) $linkValid,
        ]);
    }


    public function reset(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:255',
            'email' => 'bail|required|string|max:255|email',
            'password' => ['bail', 'required', 'string', new StrongPassword(), 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $resetAdmin = null;
        $status = $this->broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) use (&$resetAdmin) {
                $this->resetPassword($admin, $password);
                $resetAdmin = $admin;
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'errors' => ['email' => ['This reset link is invalid or has expired. Please request a new link.']],
            ], 422);
        }

        if (!app(\Modules\Restaurant\Services\RestaurantPasswordResetNotificationService::class)->sendForOwner($resetAdmin)) {
            app(NotificationService::class)->trigger('Admin', 'UserPasswordChanged', $resetAdmin, [
                'email' => $resetAdmin->email,
                'name' => $resetAdmin->name,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset successfully!',
            'redirect_url' => route('admin.login.form')
        ]);
    }


    protected function resetPassword($user, $password)
    {
        if (Hash::check($password, $user->getAuthPassword())) {
            throw ValidationException::withMessages([
                'password' => ['The new password must be different from your old password.'],
            ]);
        }

        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        app(\Modules\Acl\Services\AdminSessionRevoker::class)->revoke($user);

        event(new PasswordReset($user));
    }


    public function broker()
    {
        return Password::broker('admins');
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }

    protected function redirectPath()
    {
        return route($this->redirectTo);
    }
}
