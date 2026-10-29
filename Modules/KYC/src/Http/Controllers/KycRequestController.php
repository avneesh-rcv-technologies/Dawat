<?php

namespace Modules\KYC\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\KYC\Services\KycRequestService;
use Modules\KYC\Services\KycTypeService;
use Modules\KYC\Http\Requests\KycRequestRequest;
use Modules\KYC\DataView\KycRequestGrid;
use Modules\KYC\Traits\HandlesKycErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Acl\Models\Admin;
use Exception;

class KycRequestController extends Controller
{
    use HandlesKycErrors;
    protected $requestService;
    protected $typeService;

    public function __construct(
        KycRequestService $requestService,
        KycTypeService $typeService
    ) {
        $this->requestService = $requestService;
        $this->typeService = $typeService;
    }

    /**
     * Display listing of KYC requests
     */
    public function index(Request $request)
    {
        try {
            $lists = fn_datagrid(KycRequestGrid::class)->process();
            $statistics = $this->requestService->getStatistics();

            return view('kyc::admin.requests.index', compact('lists', 'statistics'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load KYC requests: ' . $e->getMessage());
        }
    }

    /**
     * Show form for creating new KYC request
     */
    public function create()
    {
        try {
            $kycTypes = $this->typeService->getAllTypes(true); // Active types only
            $restaurantCount = Admin::query()
                ->join('roles', 'admins.role_id', '=', 'roles.id')
                ->where('roles.guard', 'admin')
                ->where('roles.name', 'Restaurant Owner')
                ->where('admins.status', 1)
                ->count();

            return view('kyc::admin.requests.create', compact('kycTypes', 'restaurantCount'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load KYC types: ' . $e->getMessage());
        }
    }

    /**
     * Store new KYC request
     */
    public function store(KycRequestRequest $request)
    {
        try {
            $adminId = Auth::guard('admin')->id();
            if (!$adminId) {
                return back()->withInput()
                    ->with('error', 'Authentication required');
            }

            $kycRequest = $this->requestService->createRequest(
                $request->validated(),
                $adminId
            );

            return redirect()->route('admin.kyc.requests.index')
                ->with('success', 'KYC Request created and notifications sent successfully');
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create KYC Request: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete selected KYC requests
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $ids = array_filter((array) $request->input('ids', []));
            $deleted = 0;
            $skipped = 0;

            foreach ($ids as $id) {
                // Support numeric ids only
                if (!is_numeric($id)) {
                    $skipped++;
                    continue;
                }

                try {
                    $this->requestService->deleteRequest((int)$id);
                    $deleted++;
                } catch (Exception $e) {
                    // If one fails, skip and continue
                    $skipped++;
                }
            }

            $message = "Deleted {$deleted} request(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} skipped.";
            }

            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete requests: ' . $e->getMessage());
        }
    }

    /**
     * Delete KYC request
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request ID'
                ], 400);
            }

            $this->requestService->deleteRequest((int)$id);

            return $this->handleKycSuccess(null, 'KYC Request deleted successfully');
        } catch (Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->boolean('is_ajax')) {
                return response()->json([
                    'errors' => $e->getMessage(),
                ], 200);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get users for notification target
     */
    public function getUsers(Request $request)
    {
        try {
            $type = $request->input('type', 'all');
            $search = trim((string) $request->input('search', ''));

            $validTypes = ['all', 'restaurants', 'admins'];
            if (!in_array($type, $validTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user type'
                ], 400);
            }

            $users = [];

            if ($type === 'restaurants' || $type === 'all' || $type === 'admins') {
                $query = Admin::query()
                    ->join('roles', 'admins.role_id', '=', 'roles.id')
                    ->where('roles.guard', 'admin')
                    ->where('roles.name', 'Restaurant Owner')
                    ->where('admins.status', 1);

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('admins.name', 'like', "%{$search}%")
                            ->orWhere('admins.email', 'like', "%{$search}%")
                            ->orWhere('admins.phone', 'like', "%{$search}%");
                    });
                }

                $owners = $query->select('admins.id', 'admins.name', 'admins.email')
                    ->limit(50)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => 'admin_' . $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'type' => 'Restaurant Owner'
                        ];
                    });

                $users = array_merge($users, $owners->toArray());
            }

            return response()->json($users);
        } catch (Exception $e) {
            return $this->handleKycException($e, 'Get Users');
        }
    }
}
