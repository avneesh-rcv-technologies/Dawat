<?php

namespace Modules\Acl\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Acl\Services\RoleService;
use Illuminate\Support\Facades\Validator;
use Modules\Acl\DataView\RolesView;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class RolesController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        $lists = fn_datagrid(RolesView::class)->process();
        return view('acl::settings.roles.index', compact('lists'));
    }

    public function create()
    {
        return view('acl::settings.roles.form', ['role' => null]);
    }



    public function store(Request $request)
    {
        $request->merge(['guard' => 'admin']);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles|max:128',
            'description' => 'nullable|string|max:256',
            'guard' => 'required|string|max:128',
            'permission_type' => 'required|in:all,custom',
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['permissions'] = $data['permission_type'] === 'custom' ? array_values(array_unique($data['permissions'] ?? [])) : [];
            $this->roleService->store($data);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully!',
                'redirect_url' => route('settings.roles.index')
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->merge(['guard' => 'admin']);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128|unique:roles,name,' . $id,
            'description' => 'nullable|string|max:256',
            'guard' => 'sometimes|required|string|max:128',
            'permission_type' => 'required|in:all,custom',
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:128'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['permissions'] = $data['permission_type'] === 'custom' ? array_values(array_unique($data['permissions'] ?? [])) : [];
            $this->roleService->update($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully!',
                'redirect_url' => route('settings.roles.index')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function edit($id)
    {
        $role = $this->roleService->find($id);
        return view('acl::settings.roles.form', compact('role'));
    }

    public function destroy($id)
    {
        try {
            $this->roleService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully!',
                'redirect_url' => route('settings.roles.index')
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:roles,id',
        ]);
        try {
            $this->roleService->deleteMultiple($request->ids);
            return redirect()->route('settings.roles.index')->with('success', 'Roles Deleted Successfully');
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return redirect()->route('settings.roles.index')->with('error', 'Something went wrong. Please try again.');
        }
    }
}
