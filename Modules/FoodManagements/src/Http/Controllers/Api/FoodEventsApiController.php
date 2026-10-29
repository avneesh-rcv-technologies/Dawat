<?php

namespace Modules\FoodManagements\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\FoodManagements\Services\FoodEventService;

class FoodEventsApiController extends Controller
{
    protected FoodEventService $service;

    public function __construct(FoodEventService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all active food events
     */
    public function index()
    {
        try {
            $events = $this->service->getActiveEventsWithDetails();

            return response()->json([
                'success' => true,
                'data' => $events,
                'message' => 'Food events retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve food events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific event with all associated items and categories
     */
    public function show($id)
    {
        try {
            $eventData = $this->service->getEventWithAllItems($id);

            if (!$eventData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found or inactive'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $eventData,
                'message' => 'Event details retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get event by slug
     */
    public function showBySlug($slug)
    {
        try {
            // Find event by slug
            $event = \Modules\FoodManagements\Models\FoodEvent::where('slug', $slug)
                ->where('status', true)
                ->first();

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found'
                ], 404);
            }

            $eventData = $this->service->getEventWithAllItems($event->id);

            return response()->json([
                'success' => true,
                'data' => $eventData,
                'message' => 'Event details retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get food items for a specific event
     */
    public function getEventItems($id, Request $request)
    {
        try {
            $eventData = $this->service->getEventWithAllItems($id);

            if (!$eventData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found or inactive'
                ], 404);
            }

            // Apply filters if provided
            $items = collect($eventData['food_items']);

            // Filter by category if provided
            if ($request->has('category_id')) {
                $categoryId = $request->get('category_id');
                $items = $items->filter(function ($item) use ($categoryId) {
                    return collect($item['categories'])->contains('id', $categoryId);
                });
            }

            // Filter by price range if provided
            if ($request->has('min_price')) {
                $items = $items->filter(function ($item) use ($request) {
                    return $item['sale_price'] >= $request->get('min_price');
                });
            }

            if ($request->has('max_price')) {
                $items = $items->filter(function ($item) use ($request) {
                    return $item['sale_price'] <= $request->get('max_price');
                });
            }

            // Search by name if provided
            if ($request->has('search')) {
                $search = strtolower($request->get('search'));
                $items = $items->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['name']), $search) ||
                           str_contains(strtolower($item['description'] ?? ''), $search);
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            $total = $items->count();
            $items = $items->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'event' => $eventData['event'],
                    'items' => $items,
                    'categories' => $eventData['food_categories'],
                    'meta' => [
                        'current_page' => (int) $page,
                        'per_page' => (int) $perPage,
                        'total' => $total,
                        'last_page' => ceil($total / $perPage),
                        'from' => ($page - 1) * $perPage + 1,
                        'to' => min($page * $perPage, $total),
                    ]
                ],
                'message' => 'Event items retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for a specific event
     */
    public function getEventCategories($id)
    {
        try {
            $eventData = $this->service->getEventWithAllItems($id);

            if (!$eventData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event not found or inactive'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'event' => $eventData['event'],
                    'categories' => $eventData['food_categories'],
                    'meta' => $eventData['meta']
                ],
                'message' => 'Event categories retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve event categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}