<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Filemanager\Models\File;
use Modules\Filemanager\Services\FileService;

class GalleryController extends Controller
{
    public function __construct(protected FileService $fileService) {}

    /**
     * Display admin gallery management page
     */
    public function index()
    {
        return view('admin::pages.gallery.index');
    }

    /**
     * Upload admin gallery images
     */
   public function store(Request $request)
{
    $validator = Validator::make(
        $request->all(),
        [
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120',
            ],
        ],
        [
            'images.required' => 'Please select at least one image.',
            'images.array' => 'Please select valid images.',
            'images.min' => 'Please select at least one image.',
            'images.max' => 'You can upload a maximum of 10 images.',
            'images.*.required' => 'Please select an image.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Only JPEG, PNG, JPG, GIF and WEBP images are allowed.',
            'images.*.max' => 'Each image must not exceed 5 MB.',
        ]
    );

    if ($validator->fails()) {
        return redirect()
            ->route('gallery.index')
            ->with('error', $validator->errors()->first());
    }

    $validated = $validator->validated();

    try {

        /*
         * Get already uploaded gallery images.
         *
         * Replace this query with your actual
         * gallery/file model.
         */
        $existingImagesCount = File::with('fileLinks')
            ->whereHas('fileLinks', function ($query) {
                $query->where('object_type', 'admin_gallery');
            })
            ->count();

        $newImagesCount = count($request->file('images'));

        $totalImages = $existingImagesCount + $newImagesCount;

        if ($totalImages > 10) {
            $remaining = 10 - $existingImagesCount;

            if ($remaining <= 0) {
                return redirect()
                    ->route('gallery.index')
                    ->with(
                        'error',
                        'You can upload a maximum of 10 images. Please delete uploaded images first.'
                    );
            }

            return redirect()
                ->route('gallery.index')
                ->with(
                    'error',
                    "You already have {$existingImagesCount} images. You can upload only {$remaining} more image(s). Please delete uploaded images first."
                );
        }

        foreach ($request->file('images') as $index => $image) {
            $this->fileService->uploadFile(
                $image,
                'admin_gallery',
                1,
                'A',
                $index
            );
        }

        return redirect()
            ->route('gallery.index')
            ->with('success', 'Gallery images uploaded successfully.');

    } catch (\Exception $e) {

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Failed to upload images: ' . $e->getMessage());
    }
}

    /**
     * Delete admin gallery image
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->fileService->deleteFileWithId($id);

            if (!$deleted) {
                return redirect()->back()
                    ->with('error', 'Image not found or already deleted.');
            }

            return redirect()->route('admin.gallery.index')
                ->with('success', 'Image deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete image: ' . $e->getMessage());
        }
    }
}
