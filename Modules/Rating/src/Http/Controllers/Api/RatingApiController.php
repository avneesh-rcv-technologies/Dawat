<?php

namespace Modules\Rating\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Acl\Models\Customer;
use Modules\Orders\Models\Order;
use Modules\Rating\Services\RatingService;
use Modules\Rating\Models\Rating;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Restaurant\Models\Restaurant;

class RatingApiController extends Controller
{
    protected $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /** Return all ratings submitted for an order owned by the authenticated customer. */
    public function byOrder(Request $request, int $orderId)
    {
        $customer = $request->user('sanctum');
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Please sign in to view order ratings.'], 401);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $allowedCustomerTypes = array_unique([get_class($customer), Customer::class, \App\Models\User::class]);
        if ((int) $order->customer_id !== (int) $customer->getAuthIdentifier()
            || !in_array($order->customer_type, $allowedCustomerTypes, true)) {
            return response()->json(['success' => false, 'message' => 'You are not allowed to view ratings for this order.'], 403);
        }

        $ratings = Rating::with('ratable')
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get()
            ->map(function (Rating $rating) {
                $isRestaurant = $rating->ratable_type === Restaurant::class;
                $effectiveStatus = $isRestaurant
                    ? ($rating->admin_status ?: ($rating->is_approved ? 'approved' : 'pending'))
                    : (in_array($rating->admin_status, ['approved', 'rejected'], true)
                        ? $rating->admin_status
                        : ($rating->restaurant_status ?: 'pending'));

                return [
                    'id' => $rating->id,
                    'order_id' => $rating->order_id,
                    'order_item_id' => $rating->order_item_id,
                    'type' => $isRestaurant ? 'restaurant' : 'food_item',
                    'ratable_id' => $rating->ratable_id,
                    'name' => $rating->ratable?->name,
                    'rating' => (int) $rating->rating,
                    'review' => $rating->review,
                    'status' => $effectiveStatus,
                    'is_public' => (bool) ($rating->is_approved && $rating->status),
                    'restaurant_reply' => $rating->restaurant_reply,
                    'rejection_reason' => $effectiveStatus === 'rejected' ? $rating->rejection_reason : null,
                    'created_at' => $rating->created_at?->toIso8601String(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Order ratings retrieved successfully.',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'ratings' => $ratings,
                'count' => $ratings->count(),
            ],
        ]);
    }

    /**
     * Get list of ratings for a specific item.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'between:1,50'],
            'ratable_type' => ['nullable', 'string', 'required_with:ratable_id', 'in:restaurant,food_item,' . Restaurant::class . ',' . FoodItem::class],
            'ratable_id' => ['nullable', 'integer', 'required_with:ratable_type'],
        ]);

        try {
            $perPage = (int) ($validated['per_page'] ?? 10);
            
            // If ratable_type and ratable_id are provided, get ratings for that item
            if (isset($validated['ratable_type'], $validated['ratable_id'])) {
                $ratableType = $this->resolveRatableType($validated['ratable_type']);
                $ratings = $this->ratingService->getByRatable(
                    $ratableType,
                    (int) $validated['ratable_id'],
                    $perPage
                );
                
                $stats = $this->ratingService->getRatingStats(
                    $ratableType,
                    (int) $validated['ratable_id']
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
        return response()->json([
            'success' => false,
            'message' => 'Submit ratings for a delivered order using /api/v1/ratings/orders/submit.',
        ], 410);
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
            'ratable_type' => ['required', 'string', 'in:restaurant,food_item,' . Restaurant::class . ',' . FoodItem::class],
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
            $ratableType = $this->resolveRatableType($request->input('ratable_type'));
            $stats = $this->ratingService->getRatingStats(
                $ratableType,
                (int) $request->ratable_id
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
            $rating = \Modules\Rating\Models\Rating::approved()->active()->find($id);

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

    /** Accept stable mobile aliases and the model class names used by older clients. */
    private function resolveRatableType(string $type): string
    {
        return match ($type) {
            'restaurant', Restaurant::class => Restaurant::class,
            'food_item', FoodItem::class => FoodItem::class,
            default => abort(422, 'ratable_type must be restaurant or food_item.'),
        };
    }
}
