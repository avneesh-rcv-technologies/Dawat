<?php

namespace Modules\Acl\Services;

use Modules\Acl\Services\admin\UsersAdminService as AdminUsersService;

/**
 * Compatibility wrapper so controllers that type-hint `Modules\Acl\Services\UserAdminService`
 * still work. This class proxies to the admin UsersAdminService implementation.
 */
class UserAdminService extends AdminUsersService
{
    /**
     * Delete multiple users (alias for deleteBulk)
     *
     * @param array $ids
     * @return int
     */
    public function deleteMultiple(array $ids): int
    {
        return $this->deleteBulk($ids);
    }

    /**
     * Update multiple statuses for users
     *
     * @param array $ids
     * @param mixed $status
     * @return int number of updated records
     */
    public function updateStatuses(array $ids, $status): int
    {
        // simple implementation: update each and return count
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->updateStatus((int)$id, $status);
                $count++;
            } catch (\Throwable $e) {
                // ignore individual failures
            }
        }
        return $count;
    }
}
