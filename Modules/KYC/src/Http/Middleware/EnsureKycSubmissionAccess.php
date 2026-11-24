<?php

namespace Modules\KYC\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Acl\Models\Admin;
use Modules\KYC\Models\KycSubmission;
use Modules\Restaurant\Services\RestaurantApprovalAccess;
use Modules\Restaurant\Models\Restaurant;

class EnsureKycSubmissionAccess
{
    public function __construct(private RestaurantApprovalAccess $access) {}

    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user('admin');
        abort_unless($admin instanceof Admin && $admin->status, 403);
        $ownerReply = $request->routeIs('admin.kyc.submissions.reply');
        if ($ownerReply) {
            abort_unless($this->access->isOwner($admin), 403);
        } else {
            abort_unless($admin->hasPermission($request->route()->getName()), 403);
        }
        if (!$request->routeIs('admin.kyc.submissions.index')) {
            abort_unless($admin->hasPermission('admin.kyc.submissions.show'), 403);
        }

        if ($request->query->has('restaurant_id') && ($id = $request->route('submission'))) {
            $submission = KycSubmission::findOrFail($id);
            abort_unless(Restaurant::whereKey($request->query('restaurant_id'))
                ->where('admin_id', $submission->user_id)->exists(), 404);
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
