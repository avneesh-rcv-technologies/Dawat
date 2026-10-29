<?php

namespace Modules\Acl\Services\Guard;

/**
 * BaseGuardService
 *
 * A small abstract stub to implement guard-specific user services (admin, api, etc.).
 * Extend this class when creating guard-specific user management services.
 */
abstract class BaseGuardService
{
    /**
     * Create a new user for the guard
     *
     * @param array $data
     * @return mixed
     */
    abstract public function create(array $data);

    /**
     * Update a user
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    abstract public function update(int $id, array $data);

    /**
     * Delete a user
     *
     * @param int $id
     * @return bool
     */
    abstract public function delete(int $id): bool;

    /**
     * Delete multiple users
     *
     * @param array $ids
     * @return int number of deleted records
     */
    abstract public function deleteBulk(array $ids): int;

    /**
     * Update status for a user
     *
     * @param int $id
     * @param mixed $status
     * @return mixed
     */
    abstract public function updateStatus(int $id, $status);

    /**
     * Find a user by id
     *
     * @param int $id
     * @return mixed
     */
    abstract public function find(int $id);
}
