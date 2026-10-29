<?php

namespace Modules\Rating\Repositories;

use Modules\Rating\Models\Rating;
use Rcv\Core\Repositories\BaseRepository as CoreBaseRepository;

class RatingRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new Rating();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return Rating::class;
    }

    /**
     * Get all ratings
     *
     * @param array $columns
     * @return mixed
     */
    public function getAll(array $columns = ['*'])
    {
        return $this->model->all($columns);
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
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Get approved ratings
     *
     * @param int $perPage
     * @return mixed
     */
    public function getApproved(int $perPage = 15)
    {
        return $this->model->approved()->active()->paginate($perPage);
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
        return $this->model
            ->where('ratable_type', $type)
            ->where('ratable_id', $id)
            ->approved()
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get average rating for a ratable
     *
     * @param string $type
     * @param int $id
     * @return float
     */
    public function getAverageRating(string $type, int $id): float
    {
        return $this->model
            ->where('ratable_type', $type)
            ->where('ratable_id', $id)
            ->approved()
            ->active()
            ->avg('rating') ?? 0;
    }

    /**
     * Get rating count for a ratable
     *
     * @param string $type
     * @param int $id
     * @return int
     */
    public function getRatingCount(string $type, int $id): int
    {
        return $this->model
            ->where('ratable_type', $type)
            ->where('ratable_id', $id)
            ->approved()
            ->active()
            ->count();
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
        return $this->model->find($id, $columns);
    }

    /**
     * Create new rating
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update rating
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        $rating = $this->getById($id);
        if ($rating) {
            $rating->update($data);
            return $rating;
        }
        return null;
    }

    /**
     * Delete rating
     *
     * @param int $id
     * @return bool
     */
    public function delete($id): bool
    {
        $rating = $this->getById($id);
        if ($rating) {
            return $rating->delete();
        }
        return false;
    }

    /**
     * Approve rating
     *
     * @param int $id
     * @return mixed
     */
    public function approve(int $id)
    {
        return $this->update($id, ['is_approved' => true]);
    }

    /**
     * Toggle status
     *
     * @param int $id
     * @return mixed
     */
    public function toggleStatus(int $id)
    {
        $rating = $this->getById($id);
        if ($rating) {
            $rating->status = !$rating->status;
            $rating->save();
            return $rating;
        }
        return null;
    }
}
