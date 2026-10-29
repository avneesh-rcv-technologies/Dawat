<?php

namespace Modules\Acl\Http\Controllers;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;

class AdminEmailVerificationController
{
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('admin.login.form')->with('error', 'This verification link is invalid or has expired. Please sign in again to request a new link.');
        }

        $admin = Admin::find($id);
        if (!$admin || !hash_equals(sha1($admin->email), $hash)) {
            return redirect()->route('admin.login.form')->with('error', 'This verification link is no longer valid. Please sign in again to request a new link.');
        }

        if (!$admin->email_verified_at) {
            $admin->forceFill(['email_verified_at' => now()])->save();
            event(new Verified($admin));
        }

        return redirect()->route('admin.login.form')->with('success', 'Your email has been verified. You can now sign in.');
    }
}
