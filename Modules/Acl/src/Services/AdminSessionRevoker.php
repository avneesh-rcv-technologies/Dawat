<?php

namespace Modules\Acl\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Acl\Models\Admin;

class AdminSessionRevoker
{
    public function revoke(Admin $admin): void
    {
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $admin->getAuthIdentifier())
                ->delete();
        }

        $request = request();
        $guard = Auth::guard('admin');
        if ($request->hasSession() && (string) $guard->id() === (string) $admin->getAuthIdentifier()) {
            $guard->logoutCurrentDevice();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }
}
