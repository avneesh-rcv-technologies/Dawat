<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Services\CacheService;
use Modules\Restaurant\Models\Restaurant;

Route::get('/', [\Modules\Website\Http\Controllers\HomeController::class, 'index'])->name('website.home');

Route::get('/test-image-cache', function () {
    $startTime = microtime(true);
    
    // Load restaurants with images
    $restaurants = Restaurant::select('id', 'name')
        ->orderBy('id')
        ->limit(30)
        ->get();

    $images = $restaurants->map(function ($restaurant) {
        $image = CacheService::rememberImage('restaurant', $restaurant->id, function () use ($restaurant) {
            $img = fn_get_image('restaurant', $restaurant->id);
            return is_array($img) ? $img : ['url' => null];
        });

        return [
            'id' => $restaurant->id,
            'name' => $restaurant->name,
            'url' => $image['url'] ?? null,
        ];
    });

    $loadTime = round((microtime(true) - $startTime) * 1000, 2);

    $response = response()->view('frontend.test-image-cache', compact('images', 'loadTime'));
    
    // Add browser caching headers for better performance
    $response->header('Cache-Control', 'public, max-age=3600'); // 1 hour browser cache
    $response->header('ETag', md5($loadTime . count($images)));
    
    return $response;
})->name('test.image.cache');

Route::get('/clear-image-cache', function () {
    Cache::flush();
    return response()->json(['message' => 'All cache cleared', 'timestamp' => now()]);
})->name('clear.cache');

// Optimized image serving with strong cache headers
Route::get('/image/{objectType}/{objectId}', function ($objectType, $objectId) {
    $image = CacheService::rememberImage($objectType, (int)$objectId, function () use ($objectType, $objectId) {
        $img = fn_get_image($objectType, (int)$objectId);
        return is_array($img) ? $img : null;
    });

    if (!$image || !$image['url']) {
        return response()->json(['error' => 'Image not found'], 404);
    }

    // Return image URL with cache headers
    $response = response()->json([
        'url' => $image['url'],
        'cached' => true,
        'timestamp' => now()->timestamp
    ]);

    // Set strong cache headers (1 year for immutable content)
    $response->header('Cache-Control', 'public, max-age=31536000, immutable');
    $response->header('ETag', '"' . md5($objectType . $objectId . $image['url']) . '"');
    $response->header('Last-Modified', gmdate('D, d M Y H:i:s T', time() - 86400));

    return $response;
})->name('serve.image');
