<?php

namespace Modules\Rating\Services;

use Faker\Provider\Base;
use Modules\Rating\Repositories\RatingRepository;
use Illuminate\Support\Facades\Auth;

class RatingService 
{
    protected $repository;

    public function __construct(RatingRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all ratings
     *
     * @param array $columns
     * @return mixed
     */
    public function getAll(array $columns = ['*'])
    {
        return $this->repository->getAll($columns);
    }

    /**
     * Get paginated ratings
     *
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function getPaginated(int $perPage = 15, array $columns = ['*'])
    {
        return $this->repository->getPaginated($perPage, $columns);
    }

    /**
     * Get approved ratings
     *
     * @param int $perPage
     * @return mixed
     */
    public function getApproved(int $perPage = 15)
    {
        return $this->repository->getApproved($perPage);
    }

    /**
     * Get ratings by ratable
     *
     * @param string $type
     * @param int $id
     * @param int $perPage
     * @return mixed
     */
    public function getByRatable(string $type, int $id, int $perPage = 15)
    {
        return $this->repository->getByRatable($type, $id, $perPage);
    }

    /**
     * Get rating statistics for a ratable
     *
     * @param string $type
     * @param int $id
     * @return array
     */
    public function getRatingStats(string $type, int $id): array
    {
        return [
            'average' => round($this->repository->getAverageRating($type, $id), 1),
            'count' => $this->repository->getRatingCount($type, $id),
        ];
    }

    /**
     * Find rating by ID
     *
     * @param int $id
     * @param array $columns
     * @return mixed
     */
    public function getById(int $id, array $columns = ['*'])
    {
        return $this->repository->getById($id, $columns);
    }

    /**
     * Create new rating
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        // Check if user can review based on settings
        $whoCanReview = fn_get_setting('rating.who_can_review', 'both');
        
        if ($whoCanReview === 'logged_in' && !Auth::check()) {
            throw new \Exception('Only logged in users can submit reviews.');
        }

        if ($whoCanReview === 'guest' && Auth::check()) {
            throw new \Exception('Only guests can submit reviews.');
        }

        // Add user_id if authenticated
        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $data['guest_name'] = null;
            $data['guest_email'] = null;
        }

        // Add IP address
        $data['ip_address'] = request()->ip();

        // Set approval based on settings
        $requireApproval = fn_get_setting('rating.require_approval', true);
        $data['is_approved'] = !$requireApproval;

        return $this->repository->create($data);
    }

    /**
     * Update rating
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete rating
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Approve rating
     *
     * @param int $id
     * @return mixed
     */
    public function approve(int $id)
    {
        return $this->repository->approve($id);
    }

    /**
     * Toggle status
     *
     * @param int $id
     * @return mixed
     */
    public function toggleStatus(int $id)
    {
        return $this->repository->toggleStatus($id);
    }

    /**
     * Bulk delete ratings
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids): bool
    {
        foreach ($ids as $id) {
            $this->repository->delete($id);
        }
        return true;
    }
}
