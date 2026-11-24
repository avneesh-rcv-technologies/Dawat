<?php

namespace Modules\Activitylog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Modules\Acl\Models\Admin;
use Modules\Activitylog\DataView\AccessRequest;
use Modules\Activitylog\DataView\AdvanceLoggingGrid;
use Modules\Activitylog\Models\RequestActivityLogRollup;
use Modules\Activitylog\Support\ThreatProtectionCache;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Activitylog\Models\AccessRequestOtp;
use Modules\Activitylog\Models\AdminAccessRequest;
use Modules\Activitylog\Models\AllowedIp;
use Modules\Activitylog\Models\BlockedIp;
use Modules\Activitylog\Models\RequestActivityLog;
use Modules\Notifications\Services\NotificationService;
use Throwable;

class AdvanceLoggingController extends Controller
{
    private const SECONDS_SUFFIX = ' seconds.';

    public function __construct(protected NotificationService $notificationService) {}

    public function index()
    {
        $dateFrom = request('date_from', AdvanceLoggingGrid::defaultDateFrom());
        $dateTo = request('date_to', now()->toDateString());

        $lists = fn_datagrid(AdvanceLoggingGrid::class)->process();
        $stats = $this->getAdvanceLoggingStats($dateFrom, $dateTo);

        return view('activitylog::activity-log.logging', compact('lists', 'stats', 'dateFrom', 'dateTo'));
    }

    /**
     * Build summary stats for the advance logging dashboard.
     *
     * @return array<string, int|float>
     */
    protected function getAdvanceLoggingStats(string $dateFrom, string $dateTo): array
    {
        $rollupQuery = RequestActivityLogRollup::query()
            ->whereBetween('bucket_date', [$dateFrom, $dateTo])
            ->whereNotNull('bucket_hour');

        $rollupTotals = (clone $rollupQuery)
            ->selectRaw('COALESCE(SUM(total_requests), 0) as total_requests')
            ->selectRaw('COALESCE(SUM(blocked_requests), 0) as blocked_requests')
            ->selectRaw('COALESCE(SUM(error_requests), 0) as error_requests')
            ->selectRaw('COALESCE(SUM(unique_ips), 0) as unique_ips')
            ->selectRaw('COALESCE(SUM(total_duration_ms), 0) as total_duration_ms')
            ->first();

        if ($rollupTotals && (int) $rollupTotals->total_requests > 0) {
            $todayRollup = RequestActivityLogRollup::query()
                ->where('bucket_date', today()->toDateString())
                ->whereNotNull('bucket_hour')
                ->selectRaw('COALESCE(SUM(total_requests), 0) as total_requests')
                ->first();

            $avgDuration = (int) $rollupTotals->total_requests > 0
                ? (int) round(((int) $rollupTotals->total_duration_ms) / (int) $rollupTotals->total_requests)
                : 0;

            return [
                'total_requests' => (int) $rollupTotals->total_requests,
                'today_requests' => (int) ($todayRollup->total_requests ?? 0),
                'blocked_requests' => (int) $rollupTotals->blocked_requests,
                'unique_ips' => (int) $rollupTotals->unique_ips,
                'error_requests' => (int) $rollupTotals->error_requests,
                'avg_duration_ms' => $avgDuration,
            ];
        }

        $baseQuery = RequestActivityLog::query()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        return [
            'total_requests' => (clone $baseQuery)->count(),
            'today_requests' => RequestActivityLog::query()->whereDate('created_at', today())->count(),
            'blocked_requests' => (clone $baseQuery)->where('is_blocked', true)->count(),
            'unique_ips' => (clone $baseQuery)->whereNotNull('ip_address')->distinct('ip_address')->count('ip_address'),
            'error_requests' => (clone $baseQuery)->where('response_status', '>=', 400)->count(),
            'avg_duration_ms' => (int) round((clone $baseQuery)->avg('duration_ms') ?? 0),
        ];
    }

    /**
     * Blocked IPs are shown in the request log grid (it joins blocked_ips), so this entry point opens that view.
     */
    public function blockedIpsList()
    {
        return redirect()->route('activity-log.advance-logging');
    }

    /**
     * Heatmap data for requests by hour and day of week.
     */
    public function heatmapData(Request $request, string $ip)
    {
        $logs = RequestActivityLog::query()
            ->where('ip_address', $ip)
            ->whereNotNull('created_at')
            ->get(['created_at']);

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $hours = range(0, 23);
        $matrix = [];
        foreach ($days as $day) {
            $row = [];
            foreach ($hours as $hour) {
                $row[] = 0;
            }
            $matrix[$day] = $row;
        }
        foreach ($logs as $log) {
            $day = $days[$log->created_at->dayOfWeek];
            $hour = (int) $log->created_at->format('G');
            $matrix[$day][$hour]++;
        }
        $series = [];
        foreach ($days as $i => $day) {
            $series[] = [
                'name' => $day,
                'data' => array_values($matrix[$day]),
            ];
        }
        return response()->json([
            'series' => $series,
            'hours' => array_map(fn($h) => sprintf('%02d:00', $h), $hours),
            'days' => $days,
        ]);
    }

    /**
     * Provide candlestick chart data for a given IP as JSON.
     */
    public function candlestickChartData(Request $request, string $ip)
    {
        // Example: Use request logs as OHLC data grouped by hour
        $logs = RequestActivityLog::query()
            ->where('ip_address', $ip)
            ->whereNotNull('created_at')
            ->orderBy('created_at')
            ->get(['created_at', 'duration_ms', 'response_status']);

        // Group by hour and build OHLC (open, high, low, close) for duration_ms
        $grouped = $logs->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d H:00:00');
        });

        $candles = [];
        foreach ($grouped as $time => $items) {
            $open = $items->first()->duration_ms;
            $close = $items->last()->duration_ms;
            $high = $items->max('duration_ms');
            $low = $items->min('duration_ms');
            $candles[] = [
                'x' => $time,
                'y' => [
                    (float) $open,
                    (float) $high,
                    (float) $low,
                    (float) $close
                ]
            ];
        }

        return response()->json($candles);
    }

    public function candlestickChart(Request $request, string $ip)
    {
        return view('activitylog::chart.candlestick', compact('ip'));
    }

    /**
     * Show a single request log entry.
     */
    public function show(Request $request, int $id)
    {
        try {
            $log = RequestActivityLog::findOrFail($id);

            $query = RequestActivityLog::query()
                ->where('ip_address', $log->ip_address)
                ->where('causer_type', $log->causer_type)
                ->where('causer_id', $log->causer_id)
                ->latest();

            // get block ip data from blocked_ips table
            $block = BlockedIp::query()
                ->where('ip_address', $log->ip_address)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->first();
            if ($request->filled('method')) {
                $query->where('method', $request->string('method'));
            }

            if ($request->filled('status')) {
                $query->where('response_status', (int) $request->input('status'));
            }

            if ($request->filled('is_blocked')) {
                $query->where('is_blocked', (int) $request->input('is_blocked'));
            }

            if ($request->filled('route_name')) {
                $query->where('route_name', 'like', '%' . $request->string('route_name') . '%');
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->string('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->string('date_to'));
            }

            if ($request->filled('search')) {
                $search = (string) $request->string('search');

                $query->where(function ($builder) use ($search) {
                    $builder->where('path', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('referer', 'like', "%{$search}%")
                        ->orWhere('user_agent', 'like', "%{$search}%")
                        ->orWhere('blocked_reason', 'like', "%{$search}%");
                });
            }

            $requests = $query->paginate(20)->withQueryString();

            $statusOptions = RequestActivityLog::query()
                ->where('ip_address', $log->ip_address)
                ->where('causer_type', $log->causer_type)
                ->where('causer_id', $log->causer_id)
                ->whereNotNull('response_status')
                ->distinct()
                ->orderBy('response_status')
                ->pluck('response_status');

            return view('activitylog::activity-log.logging-detail', compact('log', 'requests', 'statusOptions', 'block'));
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function blockIpForm(Request $request, int $id)
    {
        $log = RequestActivityLog::findOrFail($id);

        // get block ip data from blocked_ips table
        $block = BlockedIp::query()
            ->where('ip_address', $log->ip_address)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
        return view('activitylog::activity-log.block-ip-form', compact('log', 'block'));
    }

    /**
     * Block the IP associated with the given log entry.
     */
    public function blockIp(Request $request, int $id)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:255',
                'block_duration' => 'nullable|integer|min:-1',
                'action' => 'required|in:0,1',
            ]);

            $log = RequestActivityLog::findOrFail($id);
            $admin = Auth::guard('admin')->user();
            $duration = (int) $request->integer('block_duration', 60);
            $expiresAt = $duration === -1 ? null : now()->addMinutes($duration);

            BlockedIp::updateOrCreate(
                ['ip_address' => $log->ip_address],
                [
                    'reason' => $request->input('reason', 'Manually blocked via activity log'),
                    'is_active' => $request->input('action') === '1',
                    'blocked_by_id' => $admin?->id,
                    'blocked_by_type' => $admin ? get_class($admin) : null,
                    'expires_at' => $request->input('action') === '1' ? $expiresAt : now(), // If unblocking, set expires_at to now to deactivate immediately
                ]
            );

            ThreatProtectionCache::forgetBlockedIps();

            $isBlocked = $request->input('action') === '1';
            RequestActivityLog::where('ip_address', $log->ip_address)->update(['is_blocked' => $isBlocked]);

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'IP blocked successfully.', 'redirect_url' => redirect()->back()->getTargetUrl()]);
            }

            return redirect()->back()
                ->with('success', 'IP blocked successfully.');
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    /**
     * Modal content for submitting admin access request.
     */
    public function adminAccessRequestForm(Request $request)
    {
        return view('activitylog::activity-log.admin-access-request-form', [
            'ipAddress' => (string) $request->ip(),
            'requestedPath' => (string) $request->query('requested_path', '/'),
        ]);
    }

    /**
     * Show denied admin access page from a dedicated route.
     */
    public function deniedAdminAccess(Request $request)
    {
        return response()->view('activitylog::denied-admin-access', [
            'ipAddress' => (string) $request->ip(),
            'requestedPath' => (string) $request->query('requested_path', '/'),
            'actionText' => 'Request Admin Access',
            'modalTitle' => 'Request Admin Access',
            'buttonClass' => 'bg-blue-600 text-white hover:bg-blue-700',
        ], 403);
    }

    /**
     * Store admin access request from denied page.
     */
    public function storeAdminAccessRequest(Request $request)
    {
        try {
            $ipAddress = (string) $request->ip();
            $throttleKey = sprintf('activitylog:access-request:otp:create:%s', $ipAddress);
            $response = $this->handlePendingAccessRequest($request, $ipAddress, $throttleKey);

            if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
                $seconds = RateLimiter::availableIn($throttleKey);
                $message = 'Too many OTP requests. Try again in ' . $seconds . self::SECONDS_SUFFIX;

                if ($response === null) {
                    $response = request()->ajax()
                        ? response()->json([
                            'success' => false,
                            'message' => $message,
                        ], 429)
                        : back()->withErrors(['reason' => $message])->withInput();
                }
            }

            if ($response === null) {
                RateLimiter::hit($throttleKey, 300);

                $validated = $request->validate([
                    'reason' => 'required|string|max:1000',
                    'requested_path' => 'nullable|string|max:255',
                ]);

                $guard = Auth::guard('admin');
                $user = $guard->user();

                $accessRequest = AdminAccessRequest::create([
                    'ip_address' => $ipAddress,
                    'requested_route' => $validated['requested_path'] ?? null,
                    'reason' => $validated['reason'],
                    'status' => AdminAccessRequest::STATUS_PENDING,
                    'requested_by_id' => $user?->id,
                    'requested_by_type' => $user ? get_class($user) : null,
                ]);

                $otpCode = (string) random_int(100000, 999999);
                $otp = AccessRequestOtp::create([
                    'admin_access_request_id' => $accessRequest->id,
                    'ip_address' => $ipAddress,
                    'request_token' => (string) Str::uuid(),
                    'otp_hash' => Hash::make($otpCode),
                    'expires_at' => now()->addMinutes(5),
                ]);

                $this->sendOtpNotification($accessRequest->id, $ipAddress, $otpCode);

                $redirectUrl = route('activitylog.access-request.otp.form', ['token' => $otp->request_token]);

                $response = request()->ajax()
                    ? response()->json([
                        'success' => true,
                        'message' => 'Access request submitted. Enter OTP to continue.',
                        'redirect_url' => $redirectUrl,
                    ])
                    : redirect($redirectUrl)->with('success', 'Access request submitted. Enter OTP to continue.');
            }

            return $response;
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    protected function handlePendingAccessRequest(Request $request, string $ipAddress, string $throttleKey)
    {
        $pendingRequest = AdminAccessRequest::query()
            ->where('ip_address', $ipAddress)
            ->where('status', AdminAccessRequest::STATUS_PENDING)
            ->latest('id')
            ->first();

        if (!$pendingRequest) {
            return null;
        }

        $activeOtp = AccessRequestOtp::query()
            ->where('admin_access_request_id', $pendingRequest->id)
            ->where('ip_address', $ipAddress)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$activeOtp && !RateLimiter::tooManyAttempts($throttleKey, 3)) {
            RateLimiter::hit($throttleKey, 300);

            $otpCode = (string) random_int(100000, 999999);
            $activeOtp = AccessRequestOtp::create([
                'admin_access_request_id' => $pendingRequest->id,
                'ip_address' => $ipAddress,
                'request_token' => (string) Str::uuid(),
                'otp_hash' => Hash::make($otpCode),
                'expires_at' => now()->addMinutes(5),
            ]);
            $this->sendOtpNotification($pendingRequest->id, $ipAddress, $otpCode);
        }

        if ($activeOtp) {
            $redirectUrl = route('activitylog.access-request.otp.form', ['token' => $activeOtp->request_token]);
            $message = 'Access request already generated. Continue with OTP verification.';

            return request()->ajax()
                ? response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect_url' => $redirectUrl,
                ])
                : redirect($redirectUrl)->with('success', $message);
        }

        $seconds = RateLimiter::availableIn($throttleKey);
        $message = 'Access request already generated. Try OTP request again in ' . $seconds . self::SECONDS_SUFFIX;

        return request()->ajax()
            ? response()->json([
                'success' => false,
                'message' => $message,
            ], 429)
            : back()->withErrors(['reason' => $message])->withInput();
    }

    protected function sendOtpNotification(int $accessRequestId, string $ipAddress, string $otpCode): void
    {
        $role = fn_get_setting('accessotp.default_role');
        // // Push notification — works regardless of notification_events DB config.
        // send_push_notification($admins, [
        //     'title'   => 'Admin Access OTP: ' . $ipAddress,
        //     'body'    => 'OTP: ' . $otpCode . ' — expires in 5 minutes.',
        //     'payload' => [
        //         'type'              => 'admin_access_request_otp',
        //         'click_action'      => route('admin.activity-log.access-requests'),
        //         'access_request_id' => $accessRequestId,
        //         'ip_address'        => $ipAddress,
        //         'otp'               => $otpCode,
        //     ],
        // ]);

        // Notification service (email/SMS/etc.) — requires otp_access_request event
        // to be configured in notification_events + notification_mappings tables.
        $admins = Admin::where('role_id', $role)->get();

        foreach ($admins as $admin) {
            $this->notificationService->trigger(
                'Activitylog',
                'OtpAccessRequest',
                $admin,
                [
                    'name' => $admin->name ?? '',
                    'email' => $admin->email ?? '',
                    'ip_address' => $ipAddress,
                    'otp' => $otpCode,
                    'access_request_id' => $accessRequestId,
                ]
            );
        }
    }

    /**
     * Show OTP form after access request.
     */
    public function showAccessRequestOtpForm(Request $request)
    {
        $token = (string) $request->query('token', '');

        $otpRequest = AccessRequestOtp::query()
            ->where('request_token', $token)
            ->where('ip_address', (string) $request->ip())
            ->latest('id')
            ->first();

        if (!$otpRequest) {
            abort(404);
        }

        return view('activitylog::activity-log.access-request-otp-form', [
            'token' => $otpRequest->request_token,
            'expiresAt' => $otpRequest->expires_at,
            'ipAddress' => (string) $request->ip(),
        ]);
    }

    /**
     * Verify OTP and grant temporary admin IP access.
     */
    public function verifyAccessRequestOtp(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'otp' => 'required|digits:6',
        ]);

        $ipAddress = (string) $request->ip();
        $verifyKey = sprintf('activitylog:access-request:otp:verify:%s:%s', $ipAddress, $validated['token']);
        $errorMessage = null;
        $otpRequest = null;

        if (RateLimiter::tooManyAttempts($verifyKey, 5)) {
            $seconds = RateLimiter::availableIn($verifyKey);
            $errorMessage = 'Too many OTP attempts. Try again in ' . $seconds . ' seconds.';
        }

        if ($errorMessage === null) {
            $otpRequest = AccessRequestOtp::query()
                ->where('request_token', $validated['token'])
                ->where('ip_address', $ipAddress)
                ->latest('id')
                ->first();

            if (!$otpRequest) {
                $errorMessage = 'Invalid OTP request token.';
            } elseif ($otpRequest->used_at !== null) {
                $errorMessage = 'This OTP was already used.';
            } elseif ($otpRequest->expires_at === null || now()->greaterThan($otpRequest->expires_at)) {
                $errorMessage = 'OTP has expired. Please request a new one.';
            } elseif (!Hash::check($validated['otp'], (string) $otpRequest->otp_hash)) {
                RateLimiter::hit($verifyKey, 300);
                $errorMessage = 'Invalid OTP.';
            }
        }

        if ($errorMessage !== null) {
            return back()->withErrors(['otp' => $errorMessage])->withInput();
        }

        $otpRequest->update([
            'used_at' => now(),
        ]);

        RateLimiter::clear($verifyKey);

        $accessMinutes = (int) fn_get_setting('activitylog.otp_access_minutes', 60);
        if ($accessMinutes < 1) {
            $accessMinutes = 60;
        }

        $admin = Auth::guard('admin')->user();

        AllowedIp::updateOrCreate(
            ['ip_address' => $ipAddress],
            [
                'note' => 'OTP verified via access request #' . $otpRequest->admin_access_request_id,
                'is_active' => true,
                'expires_at' => now()->addMinutes($accessMinutes),
                'approved_by_id' => $admin?->id,
                'approved_by_type' => $admin ? get_class($admin) : null,
            ]
        );

        ThreatProtectionCache::forgetAllowedAdminIps();

        if ($otpRequest->admin_access_request_id) {
            AdminAccessRequest::query()
                ->where('id', $otpRequest->admin_access_request_id)
                ->where('status', AdminAccessRequest::STATUS_PENDING)
                ->update([
                    'status' => AdminAccessRequest::STATUS_APPROVED,
                    'review_note' => 'Auto-approved via OTP verification.',
                    'reviewed_at' => now(),
                ]);
        }

        return redirect()->route('dashboard.index')
            ->with('success', 'OTP verified. You can now access the admin panel.');
    }

    /**
     * Admin list of access requests.
     */
    public function accessRequests(Request $request)
    {

        $lists = fn_datagrid(AccessRequest::class)->process();
        return view('activitylog::activity-log.access-requests', compact('lists'));
    }

    /**
     * Modal content for approving an access request.
     */
    public function approveAccessRequestForm(int $id)
    {
        $requestItem = AdminAccessRequest::findOrFail($id);

        return view('activitylog::activity-log.approve-access-request-form', compact('requestItem'));
    }

    /**
     * Approve request and add requester IP to allowed list.
     */
    public function approveAccessRequest(Request $request, int $id)
    {
        $request->validate([
            'review_note' => 'nullable|string|max:1000',
            'approval_type' => 'required|in:temporary,permanent',
            'allow_duration' => 'nullable|integer|min:1',
        ]);

        $accessRequest = AdminAccessRequest::findOrFail($id);

        if ($accessRequest->status !== AdminAccessRequest::STATUS_PENDING) {
            return back()->with('warning', 'This request has already been reviewed.');
        }

        $admin = Auth::guard('admin')->user();

        $isTemporary = $request->input('approval_type') === 'temporary';
        $expiresAt = $isTemporary
            ? now()->addMinutes((int) $request->integer('allow_duration', 60))
            : null;

        AllowedIp::updateOrCreate(
            ['ip_address' => $accessRequest->ip_address],
            [
                'note' => 'Approved from access request #' . $accessRequest->id,
                'is_active' => true,
                'expires_at' => $expiresAt,
                'approved_by_id' => $admin?->id,
                'approved_by_type' => $admin ? get_class($admin) : null,
            ]
        );

        $accessRequest->update([
            'status' => AdminAccessRequest::STATUS_APPROVED,
            'review_note' => $request->input('review_note'),
            'reviewed_by_id' => $admin?->id,
            'reviewed_by_type' => $admin ? get_class($admin) : null,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Access request approved and IP added to allowed list.');
    }

    /**
     * Reject access request.
     */
    public function rejectAccessRequest(Request $request, int $id)
    {
        $request->validate([
            'review_note' => 'nullable|string|max:1000',
        ]);

        $accessRequest = AdminAccessRequest::findOrFail($id);

        if ($accessRequest->status !== AdminAccessRequest::STATUS_PENDING) {
            return back()->with('warning', 'This request has already been reviewed.');
        }

        $admin = Auth::guard('admin')->user();

        $accessRequest->update([
            'status' => AdminAccessRequest::STATUS_REJECTED,
            'review_note' => $request->input('review_note'),
            'reviewed_by_id' => $admin?->id,
            'reviewed_by_type' => $admin ? get_class($admin) : null,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Access request rejected.');
    }

    /**
     * Revoke approved access request and deactivate allowed IP.
     */
    public function revokeAccessRequest(Request $request, int $id)
    {
        $request->validate([
            'review_note' => 'nullable|string|max:1000',
        ]);

        $accessRequest = AdminAccessRequest::findOrFail($id);
        $admin = Auth::guard('admin')->user();

        $accessRequest->update([
            'status' => AdminAccessRequest::STATUS_REVOKED,
            'review_note' => $request->input('review_note') ?: 'Access revoked by ' . ($admin?->name ?? 'system'),
            'reviewed_by_id' => $admin?->id,
            'reviewed_by_type' => $admin ? get_class($admin) : null,
            'reviewed_at' => now(),
        ]);

        AllowedIp::query()
            ->where('ip_address', $accessRequest->ip_address)
            ->update([
                'is_active' => false,
                'expires_at' => now(),
            ]);

        return back()->with('success', 'Access request revoked and IP deactivated.');
    }


    public function toggleBlocking(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required',
            'action' => 'required|in:0,1'
        ]);

        try {
            $isBlocked = (int) $request->input('action') === 1;
            $ids = $request->input('ids', []);

            $data = collect($ids)
                ->filter() // remove null/empty
                ->map(function ($ip) use ($isBlocked) {
                    return [
                        'ip_address' => $ip,
                        'is_active' => $isBlocked,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ];
                })
                ->values()
                ->toArray();

            BlockedIp::upsert(
                $data,
                ['ip_address'], // unique key
                ['is_active', 'updated_at']
            );

            ThreatProtectionCache::forgetBlockedIps();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'IP blocking status updated successfully.']);
            }
            return redirect()->back()->with('success', 'Status Updated Successfully');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function toggleAccessRequestStatus(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:admin_access_requests,id',
                'action' => 'required|in:1,2,3',
            ]);

            $accessRequestIds = $request->input('ids', []);
            $action = (int) $request->input('action');
            $admin = Auth::guard('admin')->user();
            $reviewedByType = $admin ? get_class($admin) : null;

            $accessRequests = AdminAccessRequest::query()
                ->whereIn('id', $accessRequestIds)
                ->get();

            if ($accessRequests->isEmpty()) {
                return back()->with('warning', 'No access requests found.');
            }

            foreach ($accessRequests as $accessRequest) {
                if ($action === 1) {
                    AllowedIp::updateOrCreate(
                        ['ip_address' => $accessRequest->ip_address],
                        [
                            'note' => 'Approved from access request #' . $accessRequest->id,
                            'is_active' => true,
                            'expires_at' => now()->addHour(),
                            'approved_by_id' => $admin?->id,
                            'approved_by_type' => $reviewedByType,
                        ]
                    );

                    $accessRequest->update([
                        'status' => AdminAccessRequest::STATUS_APPROVED,
                        'review_note' => 'Approved via bulk action by ' . ($admin?->name ?? 'system') . ' (default 1 hour access).',
                        'reviewed_by_id' => $admin?->id,
                        'reviewed_by_type' => $reviewedByType,
                        'reviewed_at' => now(),
                    ]);

                    continue;
                }

                if ($action === 2) {
                    $accessRequest->update([
                        'status' => AdminAccessRequest::STATUS_REJECTED,
                        'review_note' => 'Rejected via bulk action by ' . ($admin?->name ?? 'system') . '.',
                        'reviewed_by_id' => $admin?->id,
                        'reviewed_by_type' => $reviewedByType,
                        'reviewed_at' => now(),
                    ]);

                    AllowedIp::query()
                        ->where('ip_address', $accessRequest->ip_address)
                        ->update([
                            'is_active' => false,
                            'expires_at' => now(),
                        ]);

                    continue;
                }

                $accessRequest->update([
                    'status' => AdminAccessRequest::STATUS_REVOKED,
                    'review_note' => 'Access revoked by ' . ($admin?->name ?? 'system'),
                    'reviewed_by_id' => $admin?->id,
                    'reviewed_by_type' => $reviewedByType,
                    'reviewed_at' => now(),
                ]);

                AllowedIp::query()
                    ->where('ip_address', $accessRequest->ip_address)
                    ->update([
                        'is_active' => false,
                        'expires_at' => now(),
                    ]);
            }

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Access request status updated successfully.', 'redirect_url' => redirect()->back()->getTargetUrl()]);
            }
            return back()->with('success', 'Access request status updated successfully.');
        } catch (\Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }
}
