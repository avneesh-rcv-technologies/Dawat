<?php

namespace Modules\Acl\Http\Controllers\Customer;

use App\Rules\StrongPassword;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Modules\Acl\Models\Customer;
use Modules\Notifications\Services\NotificationService;

class CustomerAuthController extends Controller
{
    protected $guard = 'customer';
    protected $redirectTo = '/dashboard';
    protected $loginView = 'acl::customer.auth.login';
    protected $registerView = 'acl::customer.auth.register';
    protected $forgotPasswordView = 'acl::customer.auth.forget-password';
    protected $resetPasswordView = 'acl::customer.auth.reset-password';

    public function __construct()
    {
        $this->middleware('guest:customer')->except('logout');
    }

    public function showLoginForm()
    {
        return view($this->loginView);
    }

     public function showRegisterForm()
    {
        return view($this->registerView);

    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean',
        ]);

        if (Auth::guard($this->guard)->attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $request->filled('remember')
        )) {
            $request->session()->regenerate();
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect_url' => $this->redirectPath(),
                    'user' => Auth::guard($this->guard)->user(),
                ]);
            }

            return redirect()->intended($this->redirectPath());
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'errors' => ['email' => ['Invalid email or password']],
            ]);
        }
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard($this->guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

         if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
                'redirect_url' => route('customer.login'),
            ]);
        }
        return redirect('/customer/login');
    }

    public function showRegistrationForm()
    {
        return view($this->registerView);
    }

    public function register(Request $request)
    {
       
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
        ]);

        $user = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        
        Auth::guard($this->guard)->login($user);

        if (app(NotificationService::class)) {
            app(NotificationService::class)->trigger(
                'Acl',
                'CustomerAccountCreation',
                $user,
                ['user' => $user]
            );
        }
        
         if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'redirect_url' => $this->redirectPath(),
                'user' => $user,
            ]);
        }

        return redirect($this->redirectPath());
    }

    public function showLinkRequestForm()
    {
        return view($this->forgotPasswordView);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Find the customer by email
        $auth = Customer::where('email', $request->email)->first();

        if (!$auth) {
            if(request()->ajax()) {
                return response()->json([
                    'errors' => ['email' => [__('We can\'t find an customer with that email address.')]]
                ]);
            }

            return back()->withErrors(['email' => __('We can\'t find an customer with that email address.')]);
        }
        
        // Generate password reset token using the correct broker
        $token = $this->broker()->createToken($auth);

        // Store the token in the password_reset_tokens table
        DB::table('customer_password_reset_tokens')->updateOrInsert(
            ['email' => $auth->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // Send password reset notification
        app(NotificationService::class)->trigger(
            'Acl',
            'CustomerPasswordReset',
            $request->email,
            [
                'token' => $token,
                'email' => $auth->email,
                'name' => $auth->name
            ]
        );
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'We have emailed your password reset link!',
                'redirect_url' => route('customer.login')
            ]);
        }

        return redirect()->route('customer.login')->with('status','We have emailed your password reset link!');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view($this->resetPasswordView)->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
        ]);

        // Find the auth by email
        $auth = Customer::where('email', $request->email)->first();

        if (!$auth) {
            if(request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['email' => [__('We can\'t find an customer with that email address.')]]
                ]);
            }

            return back()->withErrors(['email' => __('We can\'t find an customer with that email address.')]);
        }

        // Get the token record from the customer_password_reset_tokens table
        $tokenData = DB::table('customer_password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Verify the token
        if (!$tokenData || !Hash::check($request->token, $tokenData->token)) {

            if(request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['email' => [__('This password reset token is invalid.')]]
                ]);
            }

            return back()->withErrors(['email' => __('This password reset token is invalid.')]);
        }

        // Check if the token is expired (default: 60 minutes)
        $expires = 60 * 60; // 1 hour in seconds
        if (time() - strtotime($tokenData->created_at) > $expires) {
            if(request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['email' => [__('This password reset token has expired.')]]
                ], 422);
            }

            return back()->withErrors(['email' => __('This password reset token has expired.')]);
        }

        // Reset the password
        $this->resetPassword($auth, $request->password);

        // Delete the used token
        DB::table('customer_password_reset_tokens')->where('email', $request->email)->delete();

        // Send password changed notification
        app(NotificationService::class)->trigger(
            'Acl',
            'CustomerPasswordChanged',
            $auth,
            [
                'email' => $auth->email,
                'name' => $auth->name
            ]
        );

        if(request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Your password has been reset successfully!',
                'redirect_url' => route('customer.login')
            ]);
        }

        return redirect()->route('customer.login')->with('status','Your password has been reset successfully!');
    }

    protected function redirectPath()
    {
        return $this->redirectTo;
    }
}
