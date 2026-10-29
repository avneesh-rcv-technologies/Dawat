<?php

namespace Modules\Acl\Repositories;

use Modules\Acl\Models\Role;

class RoleRepository
{
    public function all()
    {
        return Role::all();
    }

    public function find($id)
    {
        return Role::findOrFail($id);
    }

    public function create(array $data)
    {
        $role = new Role();
        $role->name = $data['name'];
        $role->description = $data['description'] ?? null;
        $role->guard = $data['guard'] ?? null;
        $role->permission_type = $data['permission_type'] ?? null;
        $role->permissions = $data['permission_type'] === 'custom' ? ($data['permissions'] ?? []) : [];
        $role->save();
        return $role;
    }

    public function update($id, array $data)
    {
        $role = $this->find($id);
        if ($role->isProtected()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'role' => 'The Admin role is protected and cannot be edited.',
            ]);
        }
        $role->name = $data['name'] ?? $role->name;
        $role->description = $data['description'] ?? $role->description;
        $role->guard = $data['guard'] ?? $role->guard;
        $role->permission_type = $data['permission_type'] ?? $role->permission_type;
        $selectionSubmitted = array_key_exists('permission_type', $data) || array_key_exists('permissions', $data);
        $role->permissions = $role->permission_type === 'custom'
            ? ($data['permissions'] ?? ($selectionSubmitted ? [] : ($role->permissions ?? [])))
            : [];
        $role->save();
        return $role;
    }

    public function delete($id)
    {
        $role = $this->find($id);
        return $role->delete();
    }

    public function deleteMultiple(array $ids): int
    {
        return Role::whereIn('id', $ids)->delete();
    }
}
