<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Filemanager\Services\FileService;
use Modules\Restaurant\Models\Restaurant;

class RestaurantGalleryController extends Controller
{
    public function __construct(protected FileService $fileService) {}

    /**
     * Upload restaurant gallery images
     */
    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|integer|exists:restaurants,id',
            'images' => 'required|array|max:10|min:1', // Limit to 10 images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $restaurantId = $request->input('restaurant_id');
            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $this->fileService->uploadFile(
                        $image,
                        'restaurant_gallery',
                        $restaurantId,
                        'A',
                        $index
                    );
                }
            }

            return redirect()->back()
                ->with('success', 'Gallery images uploaded successfully.')
                ->with('tab', 'gallery');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to upload images: ' . $e->getMessage())
                ->with('tab', 'gallery');
        }
    }

    /**
     * Delete restaurant gallery image (kept for AJAX delete)
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->fileService->deleteFileWithId($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found or already deleted.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
