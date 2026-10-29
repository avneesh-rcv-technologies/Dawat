<?php

namespace Modules\Rating\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Rating\DataView\RatingsGrid;
use Illuminate\Support\Facades\Validator;
use Modules\Rating\Services\RatingService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class RatingController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Display a listing of ratings.
     */
    public function index()
    {
        $lists = fn_datagrid(RatingsGrid::class)->process();
        return view('rating::index', compact('lists'));
    }

    /**
     * Show the form for creating a new rating.
     */
    public function create()
    {
        return view('rating::create');
    }

    /**
     * Store a newly created rating.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ratable_type' => 'required|string',
            'ratable_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'guest_name' => 'required_without:user_id|string|max:255',
            'guest_email' => 'required_without:user_id|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rating = $this->ratingService->create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully!',
                'data' => $rating
            ], 201);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified rating.
     */
    public function show($id)
    {
        $rating = $this->ratingService->getById($id);
        
        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rating
        ]);
    }

    /**
     * Show the form for editing the specified rating.
     */
    public function edit($id)
    {
        $rating = $this->ratingService->getById($id);
        
        if (!$rating) {
            abort(404);
        }

        return view('rating::edit', compact('rating'));
    }

    /**
     * Update the specified rating.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'is_approved' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rating = $this->ratingService->update($id, $request->all());

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully!',
                'data' => $rating
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified rating.
     */
    public function destroy($id)
    {
        try {
            $result = $this->ratingService->delete($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found',
                    'redirect_url' => route('ratings.index')
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rating deleted successfully!',
                'redirect_url' => route('ratings.index')
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'redirect_url' => route('ratings.index')
            ], 400);
        }
    }

    /**
     * Approve a rating.
     */
    public function approve($id)
    {
        try {
            $rating = $this->ratingService->approve($id);

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found',
                    'redirect_url' => route('ratings.index')
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rating approved successfully!',
                'redirect_url' => route('ratings.index')
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'redirect_url' => route('ratings.index')
            ], 400);
        }
    }

    /**
     * Toggle rating status.
     */
    public function toggleStatus($id)
    {
        try {
            $rating = $this->ratingService->toggleStatus($id);

            if (!$rating) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rating not found',
                    'redirect_url' => route('ratings.index')
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rating status updated successfully!',
                'redirect_url' => route('ratings.index')
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'redirect_url' => route('ratings.index')
            ], 400);
        }
    }

    /**
     * Bulk delete ratings.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:ratings,id'
        ]);

        if ($validator->fails()) {
            
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->ratingService->bulkDelete($request->ids);

            return response()->json([
                'success' => true,
                'message' => 'Ratings deleted successfully!',
                'redirect_url' => route('ratings.index')
            ]);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'redirect_url' => route('ratings.index')
            ], 400);
        }
    }
}
