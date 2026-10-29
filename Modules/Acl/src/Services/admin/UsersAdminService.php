<?php

namespace Modules\Acl\Services\admin;

use Modules\Acl\Models\Admin;
use Modules\Acl\Services\Guard\BaseGuardService;
use Illuminate\Support\Facades\Hash;

/**
 * UsersAdminService
 *
 * Service to manage admin users (create, update, delete, bulk delete, status, find)
 */
class UsersAdminService extends BaseGuardService
{
    /**
     * Create a new admin user
     *
     * @param array $data
     * @return Admin
     */
    public function create(array $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return Admin::create($data);
    }

    /**
     * Update an existing admin user
     *
     * @param int $id
     * @param array $data
     * @return Admin
     */
    public function update(int $id, array $data)
    {
        $admin = $this->find($id);

        if (array_key_exists('email_verified', $data)) {
            $emailChanged = isset($data['email']) && $data['email'] !== $admin->email;
            $data['email_verified_at'] = filter_var($data['email_verified'], FILTER_VALIDATE_BOOLEAN)
                ? ($emailChanged ? now() : ($admin->email_verified_at ?? now()))
                : null;
            unset($data['email_verified']);
        }

        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = Hash::make($data['password']);
        } else {
            // do not overwrite empty password
            unset($data['password']);
        }

        $admin->fill($data);
        $admin->save();

        return $admin;
    }

    /**
     * Delete an admin user
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $admin = $this->find($id);
        return (bool)$admin->delete();
    }

    /**
     * Delete multiple admin users
     *
     * @param array $ids
     * @return int number of deleted records
     */
    public function deleteBulk(array $ids): int
    {
        return Admin::whereIn('id', $ids)->delete();
    }

    /**
     * Update status for an admin user
     *
     * @param int $id
     * @param mixed $status
     * @return Admin
     */
    public function updateStatus(int $id, $status)
    {
        $admin = $this->find($id);
        $admin->status = $status;
        $admin->save();
        return $admin;
    }

    /**
     * Find admin by id
     *
     * @param int $id
     * @return Admin
     */
    public function find(int $id)
    {
        return Admin::findOrFail($id);
    }
}
