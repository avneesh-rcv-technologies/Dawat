<?php

namespace Modules\Admin\Http\Controllers;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Modules\Admin\DataView\UsersGrid;
use Illuminate\Support\Facades\Validator;
use Modules\Acl\Services\UserAdminService;
use Modules\Notifications\Services\NotificationService;

class UserAdminController extends Controller
{
    protected $userAdminService;

    public function __construct(UserAdminService $userAdminService)
    {
        $this->userAdminService = $userAdminService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(UsersGrid::class)->process();
        return view('admin::pages.users.index', compact('lists'));
    }   

    public function create()
    {
        return view('admin::pages.users.form');
    }

    public function store(Request $request)
    {
        $request->merge(['guard' => 'admin']);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'email' => 'required|email:rfc,dns|unique:admins,email',
            'password' => ['required', 'string', new StrongPassword()],
            'guard' => ['required', Rule::in(['admin'])],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('guard', 'admin')],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $user = $this->userAdminService->create($validator->validated());
            if(app(NotificationService::class)) {
                app(NotificationService::class)->trigger(
                    'Admin',
                    'User',
                    $user,
                    ['user' => $user]
                );
            }
            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully!',
                'redirect_url' => route('users.index'),
                'data' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }
    public function edit($id)
    {
        $user = $this->userAdminService->find($id); // You should already have a method like this in your service
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found.');
        }
        return view('admin::pages.users.form', compact('user'));
    }


    public function update(Request $request, $id)
    {
        $request->merge(['guard' => 'admin']);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'email' => ['required', 'email', Rule::unique('admins')->ignore($id)],
            'guard' => ['required', Rule::in(['admin'])],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('guard', 'admin')],
            'password' => ['nullable', 'string', new StrongPassword()],
            'email_verified' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $user = $this->userAdminService->update($id, $validator->validated());

            if(app(NotificationService::class)) {
                app(NotificationService::class)->trigger(
                    'Admin',
                    'UserUpdate',
                    $user,
                    ['user' => $user]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully!',
                'redirect_url' => route('users.index'),
                'data' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:admins,id',
            'action' => 'required|in:0,1'
        ]);

        try {
            $updatedCount = $this->userAdminService->updateStatuses($request->ids, $request->action);
            return redirect()->route('users.index')->with('success', 'Status Updated Successfully');
        } catch (\Throwable $e) {
            return redirect()->route('users.index')->with('error', 'Something went wrong. Please try again.');
        }
    }
}
