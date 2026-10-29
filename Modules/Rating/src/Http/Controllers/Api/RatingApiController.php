<?php

namespace Modules\Rating\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Rating\Services\RatingService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class RatingApiController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Get list of ratings for a specific item.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            
            // If ratable_type and ratable_id are provided, get ratings for that item
            if ($request->has('ratable_type') && $request->has('ratable_id')) {
                $ratings = $this->ratingService->getByRatable(
                    $request->ratable_type,
                    $request->ratable_id,
                    $perPage
                );
                
                $stats = $this->ratingService->getRatingStats(
                    $request->ratable_type,
                    $request->ratable_id
                );
            } else {
                // Get all approved ratings
                $ratings = $this->ratingService->getApproved($perPage);
                $stats = null;
            }

            return response()->json([
                'success' => true,
                'data' => $ratings,
                'stats' => $stats,
            ], 200);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new rating.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'ratable_type' => 'required|string',
            'ratable_id' => 'required|integer',
            'rating' => 'required|integer|min:' . fn_get_setting('rating.min_rating', 1) . '|max:' . fn_get_setting('rating.max_rating', 5),
        ];

        // Check if review text is required
        if (fn_get_setting('rating.require_review_text', false)) {
            $rules['review'] = 'required|string|max:1000';
        } else {
            $rules['review'] = 'nullable|string|max:1000';
        }

        // Check who can review
        $whoCanReview = fn_get_setting('rating.who_can_review', 'both');
        
        if ($whoCanReview !== 'logged_in') {
            // Guest reviewer fields
            $rules['guest_name'] = 'required|string|max:255';
            $rules['guest_email'] = 'required|email|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $rating = $this->ratingService->create($request->all());

            $message = fn_get_setting('rating.require_approval', true)
                ? 'Thank you! Your review has been submitted and is pending approval.'
                : 'Thank you! Your review has been submitted successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
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
     * Get rating statistics for a specific item.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ratable_type' => 'required|string',
            'ratable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stats = $this->ratingService->getRatingStats(
                $request->ratable_type,
                $request->ratable_id
            );

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single rating by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
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
            ], 200);
        } catch (\Throwable $e) {
            ApiExceptionHandler::handle($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
