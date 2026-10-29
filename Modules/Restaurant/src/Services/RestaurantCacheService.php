<?php

namespace Modules\Restaurant\Services;

use Modules\Core\Services\BaseCacheService;

class RestaurantCacheService extends BaseCacheService
{
    // =========================================================
    //  Cache Keys
    // =========================================================
    
    private function listKey(): string
    {
        // Avoid lists cached before status-change invalidation was enforced.
        return 'restaurants.list.v3';
    }

    private function detailKey(int $id): string
    {
        return "restaurant.{$id}";
    }

    private function validKey(int $id): string
    {
        return "restaurant.{$id}.valid";
    }

    private function galleryKey(int $id): string
    {
        return "restaurant_gallery.{$id}";
    }

    private function imageKey(int $id): string
    {
        return "image.restaurant.{$id}";
    }

    // =========================================================
    //  Cache Methods
    // =========================================================

    /**
     * Cache restaurant list
     */
    public function rememberList(callable $callback)
    {
        return $this->remember($this->listKey(), self::TTL_MEDIUM, $callback);
    }

    /**
     * Cache single restaurant with relations
     */
    public function rememberDetail(int $id, callable $callback)
    {
        return $this->remember($this->detailKey($id), self::TTL_LONG, $callback);
    }

    /**
     * Cache restaurant open/close validation
     */
    public function rememberValid(int $id, callable $callback)
    {
        return $this->remember($this->validKey($id), self::TTL_SHORT, $callback);
    }

    /**
     * Cache restaurant gallery images
     */
    public function rememberGallery(int $id, callable $callback)
    {
        return $this->remember($this->galleryKey($id), self::TTL_4_HOURS, $callback);
    }

    /**
     * Cache restaurant image
     */
    public function rememberImage(int $id, callable $callback)
    {
        return $this->remember($this->imageKey($id), self::TTL_MEDIUM, $callback);
    }

    // =========================================================
    //  Invalidation Methods
    // =========================================================

    /**
     * Invalidate restaurant list cache
     */
    public function forgetList(): void
    {
        $this->forget($this->listKey());
    }

    /**
     * Invalidate single restaurant cache
     */
    public function forgetDetail(int $id): void
    {
        $this->forget($this->detailKey($id));
        $this->forget($this->validKey($id));
        $this->forget($this->galleryKey($id));
        $this->forget($this->imageKey($id));
    }

    /**
     * Invalidate restaurant gallery cache
     */
    public function forgetGallery(int $id): void
    {
        $this->forget($this->galleryKey($id));
    }

    /**
     * Invalidate restaurant image cache
     */
    public function forgetImage(int $id): void
    {
        $this->forget($this->imageKey($id));
    }
}
