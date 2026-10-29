<?php

namespace Modules\Acl\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Acl\Models\Role;
use Modules\Acl\Http\Resources\RoleResource;
use Modules\Acl\Http\Resources\RoleCollection;
use Modules\Acl\Http\Requests\Role\StoreRoleRequest;
use Modules\Acl\Http\Requests\Role\UpdateRoleRequest;
use Modules\Acl\Services\RoleService;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        
        $this->middleware('auth:api')->except(['index', 'show']);
        $this->middleware('permission:view_roles', ['only' => ['index', 'show']]);
        $this->middleware('permission:create_roles', ['only' => ['store']]);
        $this->middleware('permission:edit_roles', ['only' => ['update']]);
        $this->middleware('permission:delete_roles', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of roles.
     *
     * @param Request $request
     * @return RoleCollection
     */
    public function index(Request $request): RoleCollection
    {
        $perPage = $request->input('per_page', 15);
        $roles = $this->roleService->listRoles($perPage);
        
        return new RoleCollection($roles);
    }

    /**
     * Store a newly created role.
     *
     * @param StoreRoleRequest $request
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->store($request->validated());
        
        return response()->json([
            'message' => 'Role created successfully',
            'data' => new RoleResource($role)
        ], 201);
    }

    /**
     * Display the specified role.
     *
     * @param Role $role
     * @return RoleResource
     */
    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }

    /**
     * Update the specified role.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return JsonResponse
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $updatedRole = $this->roleService->update($role, $request->validated());
        
        return response()->json([
            'message' => 'Role updated successfully',
            'data' => new RoleResource($updatedRole)
        ]);
    }

    /**
     * Remove the specified role.
     *
     * @param Role $role
     * @return JsonResponse
     */
    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->delete($role);
        
        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
} 