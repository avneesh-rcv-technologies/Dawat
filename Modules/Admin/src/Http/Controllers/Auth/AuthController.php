<?php

namespace Modules\Admin\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Modules\Acl\Http\Controllers\AdminAuthController as AclAdminAuthController;

/**
 * Admin AuthController - Uses ACL Module for Authentication
 * 
 * This controller handles the presentation layer (views) while delegating
 * authentication logic to the ACL module's AdminAuthController.
 * 
 * GET routes return views, POST routes return JSON responses via parent class.
 */
class AuthController extends AclAdminAuthController
{
    /**
     * Constructor - Don't apply middleware here since it's handled by routes
     */
    public function __construct()
    {
        // Middleware is applied at route level, not here
        // This prevents "undefined method middleware()" errors
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('admin::auth.login');
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('admin::auth.register');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('admin::auth.passwords.email');
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm(Request $request)
    {
        return parent::showResetPasswordForm($request);
    }
}

