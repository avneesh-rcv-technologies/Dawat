<?php

namespace Modules\Filemanager\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Filemanager\Models\FileLink;
use Modules\Filemanager\Services\FileService;

class HomeController extends Controller
{
    use AuthorizesRequests;

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
            $fileLink = FileLink::query()->findOrFail($id);
            $this->authorize('delete', $fileLink);

            $this->fileService->deleteFileWithId($id);

            return response()->json([
                'success' => true,
                'message' => 'Image Deleted successfully!',
                'redirect_url' => url()->previous(),
            ]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        } catch (Exception $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }
}
