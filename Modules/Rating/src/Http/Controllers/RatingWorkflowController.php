<?php

namespace Modules\Rating\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Admin;
use Modules\Acl\Models\Customer;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Orders\Models\Order;
use Modules\Rating\Models\Rating;
use Modules\Rating\DataView\RatingsGrid;
use Modules\Rating\Notifications\RatingWorkflowNotification;
use Modules\Restaurant\Models\Restaurant;

class RatingWorkflowController extends Controller
{
    public function customerForm(int $orderId)
    {
        $customer = auth('customer')->user();
        abort_unless($customer, 401);
        $order = Order::with('items')->findOrFail($orderId);
        abort_unless((int) $order->customer_id === (int) $customer->getAuthIdentifier()
            && $order->customer_type === Customer::class, 403);
        abort_unless($order->status === Order::STATUS_DELIVERED, 404);
        abort_unless($order->vendor_type === Restaurant::class, 404);
        $restaurant = Restaurant::findOrFail($order->vendor_id);
        $items = $order->items->filter(fn($item) => $item->item_type === FoodItem::class && FoodItem::whereKey($item->item_id)->where('restaurant_id', $restaurant->id)->exists());
        $restaurantAlreadyRated = Rating::where('order_id', $order->id)->where('order_subject_key', 'restaurant')->exists();
        $ratedItemIds = Rating::where('order_id', $order->id)->whereNotNull('order_item_id')->pluck('order_item_id')->map(fn($id) => (int) $id)->all();
        return view('rating::workflow.customer', compact('order', 'restaurant', 'items', 'restaurantAlreadyRated', 'ratedItemIds'));
    }

    public function orderForm(Request $request, int $orderId)
    {
        $customer = auth('sanctum')->user() ?? auth('customer')->user() ?? auth()->user();
        if (!$customer) {
            return $this->ratingError($request, 'AUTHENTICATION_REQUIRED', 'Please sign in to rate an order.', 401);
        }
        $order = Order::with('items')->find($orderId);
        if (!$order) {
            return $this->ratingError($request, 'ORDER_NOT_FOUND', 'We could not find that order.', 404, ['order_id' => ['We could not find that order.']]);
        }
        if ((int) $order->customer_id !== (int) $customer->getAuthIdentifier()
            || !in_array($order->customer_type, [get_class($customer), Customer::class, \App\Models\User::class], true)) {
            return $this->ratingError($request, 'ORDER_ACCESS_DENIED', 'This order is not linked to your account.', 403, ['order_id' => ['This order is not linked to your account.']]);
        }
        if ($order->status !== Order::STATUS_DELIVERED) {
            $message = 'You can rate this order after it has been delivered or pickup is complete.';

            return $this->ratingError($request, 'ORDER_NOT_DELIVERED', $message, 422, ['order_id' => [$message]]);
        }
        $restaurant = $order->vendor_type === Restaurant::class ? Restaurant::find($order->vendor_id) : null;
        if (!$restaurant) {
            return $this->ratingError($request, 'RESTAURANT_NOT_FOUND', 'This order is not linked to an available restaurant.', 422, ['order_id' => ['This order is not linked to an available restaurant.']]);
        }
        $ratedItems = Rating::where('order_id', $order->id)->whereNotNull('order_item_id')->pluck('order_item_id')->all();
        $items = $order->items->filter(fn($item) => $item->item_type === FoodItem::class && FoodItem::whereKey($item->item_id)->where('restaurant_id', $restaurant->id)->exists())
            ->map(fn($item) => ['order_item_id' => $item->id, 'name' => $item->item_name, 'already_rated' => in_array($item->id, $ratedItems, true)])->values();
        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'restaurant' => ['id' => $restaurant->id, 'name' => $restaurant->name],
                'restaurant_already_rated' => Rating::where('order_id', $order->id)->where('order_subject_key', 'restaurant')->exists(),
                'food_items' => $items,
            ]
        ]);
    }

    public function adminIndex(Request $request)
    {
        abort_unless($this->isSuperAdmin(), 403);
        $request->validate([
            'kind' => ['nullable', 'in:restaurant'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'order_id' => ['nullable', 'integer'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'restaurant_id' => ['nullable', 'integer'],
            'food_item_id' => ['nullable', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $restaurantRatings = Rating::where('ratable_type', Restaurant::class);
        $counts = [
            'all' => (clone $restaurantRatings)->count(),
            'pending' => (clone $restaurantRatings)->where('admin_status', 'pending')->count(),
            'approved' => (clone $restaurantRatings)->approved()->active()->count(),
            'rejected' => (clone $restaurantRatings)->where('admin_status', 'rejected')->count(),
        ];

        $lists = fn_datagrid(RatingsGrid::class)->process();
        return view('rating::workflow.admin', compact('lists', 'counts'));
    }

    public function ownerIndex()
    {
        $restaurant = $this->ownedRestaurant();
        $lists = fn_datagrid(RatingsGrid::class)->process();
        $foodRatings = Rating::where('ratable_type', FoodItem::class)
            ->whereHasMorph('ratable', [FoodItem::class], fn($food) => $food->where('restaurant_id', $restaurant->id));
        $counts = [
            'all' => (clone $foodRatings)->count(),
            'pending' => (clone $foodRatings)->where('admin_status', 'pending')->where('restaurant_status', 'pending')->count(),
            'approved' => (clone $foodRatings)->approved()->active()->count(),
            'rejected' => (clone $foodRatings)->where(function ($query) {
                $query->where('admin_status', 'rejected')->orWhere(function ($ownerDecision) {
                    $ownerDecision->where('admin_status', '!=', 'approved')->where('restaurant_status', 'rejected');
                });
            })->count(),
        ];
        return view('rating::workflow.owner', compact('lists', 'restaurant', 'counts'));
    }

    public function adminReviewDetails(int $id)
    {
        abort_unless($this->isSuperAdmin(), 403);
        $rating = Rating::with(['ratable', 'user', 'order.customer'])->findOrFail($id);

        return view('rating::workflow.details', ['rating' => $rating, 'owner' => false]);
    }

    public function ownerReviewDetails(int $id)
    {
        $restaurant = $this->ownedRestaurant();
        $rating = Rating::with(['ratable', 'user', 'order.customer'])->findOrFail($id);
        $belongsToRestaurant = ($rating->ratable_type === FoodItem::class
                && (int) $rating->ratable?->restaurant_id === (int) $restaurant->id)
            || ($rating->ratable_type === Restaurant::class
                && (int) $rating->ratable_id === (int) $restaurant->id
                && $rating->admin_status === 'approved'
                && $rating->status);
        abort_unless($belongsToRestaurant, 404);

        return view('rating::workflow.details', ['rating' => $rating, 'owner' => true]);
    }

    public function submit(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'order_id' => ['required', 'integer'],
            'restaurant_rating' => ['nullable', 'integer', 'between:1,5'],
            'restaurant_review' => ['nullable', 'string', 'max:2000'],
            'food_ratings' => ['nullable', 'array'],
            'food_ratings.*' => ['array'],
            'food_ratings.*.rating' => ['nullable', 'integer', 'between:1,5'],
            'food_ratings.*.review' => ['nullable', 'string', 'max:2000'],
        ]);
        if ($validator->fails()) {
            return $this->ratingError($request, 'VALIDATION_FAILED', 'Please check the rating details and try again.', 422, $validator->errors()->toArray());
        }
        $customer = auth('sanctum')->user() ?? auth('customer')->user() ?? auth()->user();
        if (!$customer) {
            return $this->ratingError($request, 'AUTHENTICATION_REQUIRED', 'Please sign in to submit a rating.', 401);
        }
        $order = Order::with('items')->find($request->integer('order_id'));
        if (!$order) {
            return $this->ratingError($request, 'ORDER_NOT_FOUND', 'We could not find that order.', 404, ['order_id' => ['We could not find that order.']]);
        }
        if ((int) $order->customer_id !== (int) $customer->getAuthIdentifier()
            || !in_array($order->customer_type, [get_class($customer), Customer::class, \App\Models\User::class], true)) {
            return $this->ratingError($request, 'ORDER_ACCESS_DENIED', 'This order is not linked to your account.', 403, ['order_id' => ['This order is not linked to your account.']]);
        }
        if ($order->status !== Order::STATUS_DELIVERED) {
            $message = 'You can rate this order after it has been delivered or pickup is complete.';
            return $this->ratingError($request, 'ORDER_NOT_DELIVERED', $message, 422, ['order_id' => [$message]]);
        }

        $restaurant = $order->vendor_type === Restaurant::class ? Restaurant::find($order->vendor_id) : null;
        if (!$restaurant) {
            return $this->ratingError($request, 'RESTAURANT_NOT_FOUND', 'This order is not linked to an available restaurant.', 422, ['order_id' => ['This order is not linked to an available restaurant.']]);
        }
        if (Rating::where('order_id', $order->id)->where('order_subject_key', 'restaurant')->exists()) {
            $message = 'You have already submitted a restaurant rating for this order.';
            return $this->ratingError($request, 'RESTAURANT_ALREADY_RATED', $message, 422, ['order_id' => [$message]]);
        }
        $foodRatings = $request->input('food_ratings', []);
        $verifiedFoodItems = [];
        foreach ($foodRatings as $id => $values) {
            if (empty($values['rating'])) {
                if (!empty($values['review'])) {
                    $message = 'Choose a star rating before writing a food item review.';
                    return $this->ratingError($request, 'FOOD_RATING_REQUIRED', $message, 422, ["food_ratings.$id.rating" => [$message]]);
                }
                unset($foodRatings[$id]);
            }
        }
        foreach ($foodRatings as $id => $values) {
            $item = $order->items->firstWhere('id', (int) $id);
            if (!$item || $item->item_type !== FoodItem::class) {
                $message = 'This item is not part of the order. Refresh the order details and select one of its food items.';
                return $this->ratingError($request, 'FOOD_ITEM_NOT_IN_ORDER', $message, 422, ["food_ratings.$id" => [$message]]);
            }
            $food = FoodItem::whereKey($item->item_id)->where('restaurant_id', $restaurant->id)->first();
            if (!$food) {
                $message = 'This food item is no longer available for the restaurant in this order.';
                return $this->ratingError($request, 'FOOD_ITEM_UNAVAILABLE', $message, 422, ["food_ratings.$id" => [$message]]);
            }
            $verifiedFoodItems[(int) $id] = $food;
            if (Rating::where('order_id', $order->id)->where('order_subject_key', 'order_item_' . (int) $id)->exists()) {
                $message = 'You have already rated this food item for this order.';
                return $this->ratingError($request, 'FOOD_ITEM_ALREADY_RATED', $message, 422, ["food_ratings.$id" => [$message]]);
            }
        }

        try {
            $created = DB::transaction(function () use ($request, $order, $restaurant, $customer, $foodRatings, $verifiedFoodItems) {
                $rows = [];
                $rows[] = Rating::create([
                    'ratable_type' => Restaurant::class,
                    'ratable_id' => $restaurant->id,
                    'order_id' => $order->id,
                    'order_subject_key' => 'restaurant',
                    'user_id' => $customer->getAuthIdentifier(),
                    'rating' => $request->integer('restaurant_rating'),
                    'review' => $request->input('restaurant_review'),
                    'ip_address' => $request->ip(),
                    'admin_status' => 'pending',
                    'restaurant_status' => 'pending',
                    'is_approved' => false,
                ]);
                foreach ($foodRatings as $orderItemId => $values) {
                    $item = $order->items->firstWhere('id', (int) $orderItemId);
                    $food = $verifiedFoodItems[(int) $orderItemId];
                    $rows[] = Rating::create([
                        'ratable_type' => FoodItem::class,
                        'ratable_id' => $food->id,
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'order_subject_key' => 'order_item_' . $item->id,
                        'user_id' => $customer->getAuthIdentifier(),
                        'rating' => $values['rating'],
                        'review' => $values['review'] ?? null,
                        'ip_address' => $request->ip(),
                        'admin_status' => 'pending',
                        'restaurant_status' => 'pending',
                        'is_approved' => false,
                    ]);
                }
                return $rows;
            });
        } catch (\Throwable $exception) {
            Log::error('Customer rating submission failed.', [
                'order_id' => $order->id,
                'customer_id' => $customer->getAuthIdentifier(),
                'exception' => $exception->getMessage(),
            ]);

            return $this->ratingError(
                $request,
                'RATING_SUBMISSION_FAILED',
                'We could not save your rating right now. Please try again.',
                500
            );
        }
        $isApiRequest = $request->expectsJson() || $request->is('api/*');
        if (!$isApiRequest) {
            return back()->with('success', 'Your ratings were submitted for review.');
        }
        return response()->json([
            'success' => true,
            'message' => 'Your ratings were submitted for review.',
            'data' => collect($created)->map(fn(Rating $rating) => [
                'id' => $rating->id,
                'type' => $rating->ratable_type === Restaurant::class ? 'restaurant' : 'food_item',
                'ratable_id' => $rating->ratable_id,
                'order_id' => $rating->order_id,
                'order_item_id' => $rating->order_item_id,
                'rating' => $rating->rating,
                'review' => $rating->review,
                'status' => $rating->admin_status,
            ])->values(),
        ], 201);
    }

    public function decideAdmin(Request $request, int $id)
    {
        abort_unless($this->isSuperAdmin(), 403);
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected'], 'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000']]);
        $rating = Rating::with(['user', 'order.customer'])->findOrFail($id);
        $rating->admin_status = $data['decision'];
        $rating->admin_approved_by = auth('admin')->id();
        $rating->admin_approved_at = now();
        $rating->rejection_reason = $data['decision'] === 'rejected' ? ($data['rejection_reason'] ?? null) : null;
        if ($rating->ratable_type === Restaurant::class || $data['decision'] === 'approved' || $data['decision'] === 'rejected')
            $rating->is_approved = $data['decision'] === 'approved';
        $rating->save();
        return back()->with('success', 'Rating decision saved.');
    }

    public function quickApprove(Request $request, int $id)
    {
        return $this->decideAdmin(new Request(['decision' => 'approved']), $id);
    }

    public function quickReject(Request $request, int $id)
    {
        return $this->decideAdmin(new Request(['decision' => 'rejected', 'rejection_reason' => $request->input('rejection_reason')]), $id);
    }

    public function decideOwner(Request $request, int $id)
    {
        $restaurant = $this->ownedRestaurant();
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected'], 'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000']]);
        $rating = Rating::with(['user', 'order.customer'])->where('ratable_type', FoodItem::class)->whereHasMorph('ratable', [FoodItem::class], fn($q) => $q->where('restaurant_id', $restaurant->id))->findOrFail($id);
        $rating->restaurant_status = $data['decision'];
        $rating->restaurant_approved_by = auth('admin')->id();
        $rating->restaurant_approved_at = now();
        $rating->rejection_reason = $data['decision'] === 'rejected' ? ($data['rejection_reason'] ?? null) : null;
        // A super-admin approval remains the effective override.
        $rating->is_approved = $rating->admin_status !== 'rejected' && ($rating->admin_status === 'approved' || $data['decision'] === 'approved');
        $rating->save();
        return back()->with('success', 'Food item rating decision saved.');
    }

    public function reply(Request $request, int $id)
    {
        $restaurant = $this->ownedRestaurant();
        $request->validate(['restaurant_reply' => ['required', 'string', 'max:2000']]);
        $rating = Rating::where(function ($query) use ($restaurant) {
            $query->where(function ($food) use ($restaurant) {
                $food->where('ratable_type', FoodItem::class)->whereHasMorph('ratable', [FoodItem::class], fn($q) => $q->where('restaurant_id', $restaurant->id));
            })->orWhere(function ($restaurantRating) use ($restaurant) {
                $restaurantRating->where('ratable_type', Restaurant::class)->where('ratable_id', $restaurant->id)->where('admin_status', 'approved');
            });
        })->where(function ($query) {
            $query->where('restaurant_status', 'approved')->orWhere('admin_status', 'approved');
        })->findOrFail($id);
        $rating->update(['restaurant_reply' => $request->restaurant_reply, 'restaurant_replied_at' => now()]);
        return back()->with('success', 'Reply posted.');
    }

    private function ownedRestaurant(): Restaurant
    {
        $admin = auth('admin')->user();
        abort_unless($admin, 401);
        return Restaurant::where('admin_id', $admin->id)->firstOrFail();
    }

    private function isSuperAdmin(): bool
    {
        $admin = auth('admin')->user();
        return $admin && ($admin->role?->permission_type === 'all' || $admin->hasPermission('ratings.workflow.manage'));
    }

    /** Return clear API errors and normal field errors for the browser form. */
    private function ratingError(Request $request, string $code, string $message, int $status = 422, array $errors = [])
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'code' => $code,
                'message' => $message,
                'errors' => (object) $errors,
            ], $status);
        }

        return back()->withInput()->withErrors($errors ?: ['rating' => $message]);
    }

    /** Notifications must never roll back or fail a completed rating workflow action. */
    private function notifySafely($notifiable, RatingWorkflowNotification $notification, string $context): void
    {
        if (!$notifiable) {
            return;
        }

        try {
            $notifiable->notify($notification);
        } catch (\Throwable $exception) {
            Log::warning('Rating workflow notification could not be sent.', [
                'context' => $context,
                'notifiable_id' => $notifiable->getKey() ?? null,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function notifyRatingAdmins(RatingWorkflowNotification $notification, string $context): void
    {
        try {
            Admin::with('role')->get()
                ->filter(fn($admin) => $admin->role?->permission_type === 'all' || $admin->hasPermission('ratings.workflow.manage'))
                ->each(fn($admin) => $this->notifySafely($admin, $notification, $context));
        } catch (\Throwable $exception) {
            Log::warning('Rating workflow admin notifications could not be dispatched.', [
                'context' => $context,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
