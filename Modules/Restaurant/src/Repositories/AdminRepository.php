<?php

namespace Modules\Restaurant\Repositories;

use Modules\Acl\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminRepository
{
    public function create(array $data): Admin
    {
        return Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => 2, // Restaurant owner role
            'status' => true,
        ]);
    }
    public function update(int $id, array $data): bool
    {
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        return Admin::where('id', $id)->update($updateData);
    }


    public function delete(int $id): bool
    {
        return Admin::where('id', $id)->delete();
    }

    public function isAdminUsedElsewhere(int $adminId): bool
    {
        return \Modules\Restaurant\Models\Restaurant::where('admin_id', $adminId)->exists();
    }

    public function deleteUnusedAdmins(array $adminIds): void
    {
        foreach ($adminIds as $adminId) {
            if ($adminId && !$this->isAdminUsedElsewhere($adminId)) {
                $this->delete($adminId);
            }
        }
    }
}
