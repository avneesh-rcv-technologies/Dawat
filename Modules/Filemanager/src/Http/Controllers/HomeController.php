<?php

namespace Modules\Filemanager\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Filemanager\Services\FileService;

class HomeController extends Controller
{

    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deleteFile(Request $request, $id)
    {
        try {
            // Delete image if exists
            $this->fileService->deleteFileWithId($id);

            return response()->json([
                'success' => true,
                'message' => 'Image Deleted successfully!',
                'redirect_url' => url()->previous(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }
}
