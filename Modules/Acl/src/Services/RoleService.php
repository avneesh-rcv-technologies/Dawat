<?php

namespace Modules\Acl\Services;

use Modules\Acl\Repositories\RoleRepository;

class RoleService
{
    protected RoleRepository $role;

    public function __construct(RoleRepository $role)
    {
        $this->role = $role;
    }

    public function listRoles()
    {
        return $this->role->all();
    }

    public function store(array $data)
    {
        return $this->role->create($data);
    }

    public function update($roleId, array $data)
    {
        return $this->role->update($roleId, $data);
    }

    public function find($roleId)
    {
        return $this->role->find($roleId);
    }

    public function delete($roleId)
    {
        return (bool)$this->role->delete($roleId);
    }

    public function deleteMultiple(array $ids): int
    {
        return $this->role->deleteMultiple($ids);
    }
}
