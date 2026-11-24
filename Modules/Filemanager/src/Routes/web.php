<?php

use Illuminate\Support\Facades\Route;
use Modules\Filemanager\Services\FileService;
use Modules\Filemanager\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('web')->group(function () {
    Route::get('private-images/{file}/{variant?}', function (int $file, string $variant = 'original') {
        $image = app(FileService::class)->getPrivateImageContents($file, $variant);

        if (request()->headers->get('If-None-Match') === '"' . $image['etag'] . '"') {
            return response('', 304, [
                'ETag' => '"' . $image['etag'] . '"',
                'Cache-Control' => 'private, no-store, max-age=0',
            ]);
        }

        return response($image['contents'], 200, [
            'Content-Type' => $image['mime_type'],
            'Content-Disposition' => 'inline; filename="' . addslashes($image['file_name']) . '"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'ETag' => '"' . $image['etag'] . '"',
        ]);
    })->whereNumber('file')->where('variant', 'original|thumbnails|medium|large')
        ->name('filemanager.private-image');

    Route::middleware(['check.permission'])->group(function () {
        Route::delete('filemanager/{id}/delete', [HomeController::class, 'deleteFile'])->name('filemanager.delete');
    });
});
// HomeController will be generated automatically by the module generator 
