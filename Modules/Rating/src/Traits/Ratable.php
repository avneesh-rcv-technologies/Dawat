<?php

namespace Modules\Rating\Traits;

use Modules\Rating\Models\Rating;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Ratable
{
    /**
     * Get all ratings for the model.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratable');
    }

    /**
     * Get approved ratings only.
     */
    public function approvedRatings(): MorphMany
    {
        return $this->ratings()->where('is_approved', true)->where('status', true);
    }

    /**
     * Get average rating.
     */
    public function averageRating(): float
    {
        return round($this->approvedRatings()->avg('rating') ?? 0, 1);
    }

    /**
     * Get rating count.
     */
    public function ratingCount(): int
    {
        return $this->approvedRatings()->count();
    }

    /**
     * Get rating distribution (count of each star rating).
     */
    public function ratingDistribution(): array
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->approvedRatings()->where('rating', $i)->count();
        }
        return $distribution;
    }

    /**
     * Get rating statistics.
     */
    public function ratingStats(): array
    {
        return [
            'average' => $this->averageRating(),
            'count' => $this->ratingCount(),
            'distribution' => $this->ratingDistribution(),
        ];
    }

    /**
     * Check if user has rated this model.
     */
    public function hasRatedBy($userId): bool
    {
        return $this->ratings()->where('user_id', $userId)->exists();
    }

    /**
     * Get user's rating for this model.
     */
    public function ratingBy($userId)
    {
        return $this->ratings()->where('user_id', $userId)->first();
    }
}
