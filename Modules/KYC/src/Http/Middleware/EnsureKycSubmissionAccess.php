<?php

namespace Modules\KYC\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\KYC\Models\KycSubmission;
use Modules\Restaurant\Services\RestaurantApprovalAccess;

class EnsureKycSubmissionAccess
{
    public function __construct(private RestaurantApprovalAccess $access) {}

    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user('admin');
        abort_unless($admin instanceof Admin && $admin->status, 403);
        abort_unless($admin->hasPermission($request->route()->getName()), 403);
        if (!$request->routeIs('admin.kyc.submissions.index')) {
            abort_unless($admin->hasPermission('admin.kyc.submissions.show'), 403);
        }

        if ($this->access->isOwner($admin)) {
            if ($id = $request->route('submission')) {
                KycSubmission::where('user_id', $admin->id)->whereKey($id)->firstOrFail();
            }
            if ($request->routeIs('admin.kyc.submissions.bulk-review')) {
                $validated = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['required', 'integer', 'distinct']]);
                abort_unless(KycSubmission::where('user_id', $admin->id)->whereIn('id', $validated['ids'])->count() === count($validated['ids']), 403);
            }
            if ($request->has('user_id')) {
                abort_unless((string) $request->input('user_id') === (string) $admin->id, 403);
            }
        }

        return $next($request);
    }
}
