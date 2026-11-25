<?php

namespace Modules\Rating\DataView;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;
use Modules\FoodManagements\Models\FoodItem;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\RestaurantOwnerRoleResolver;

class RatingsGrid extends DataGrid
{
    private ?int $ownerRestaurantId = null;
    private bool $ownerContextResolved = false;
    protected $primaryColumn = 'id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';

    public function prepareQueryBuilder()
    {
        $ownerRestaurantId = $this->getOwnerRestaurantId();
        $query = DB::table('ratings')
            ->leftJoin('users', 'ratings.user_id', '=', 'users.id')
            ->leftJoin('orders as rating_order', 'rating_order.id', '=', 'ratings.order_id')
            ->leftJoin('customers as rating_customer', function ($join) {
                $join->on('rating_customer.id', '=', 'rating_order.customer_id')
                    ->where('rating_order.customer_type', '=', \Modules\Acl\Models\Customer::class);
            })
            ->leftJoin('restaurants as rated_restaurant', function ($join) {
                $join->on('rated_restaurant.id', '=', 'ratings.ratable_id')
                    ->where('ratings.ratable_type', '=', Restaurant::class);
            })
            ->leftJoin('food_items as rated_food', function ($join) {
                $join->on('rated_food.id', '=', 'ratings.ratable_id')
                    ->where('ratings.ratable_type', '=', FoodItem::class);
            })
            ->select(
                'ratings.id',
                'ratings.ratable_type',
                'ratings.ratable_id',
                'ratings.user_id',
                'ratings.guest_name',
                'ratings.guest_email',
                'ratings.rating',
                'ratings.review',
                'ratings.is_approved',
                'ratings.admin_status',
                'ratings.restaurant_status',
                'ratings.status',
                'ratings.order_id',
                'ratings.created_at',
                'ratings.rejection_reason',
                'ratings.restaurant_reply',
                'rating_order.order_number',
                'users.name as user_name',
                'users.email as user_email',
                'rating_customer.name as customer_name',
                'rating_customer.email as customer_email',
                'rated_restaurant.name as restaurant_name',
                'rated_food.name as food_name',
                DB::raw('COALESCE(rated_food.name, rated_restaurant.name) as entity_name')
            );

        if ($ownerRestaurantId) {
            $query->where('ratings.ratable_type', FoodItem::class)
                ->where('rated_food.restaurant_id', $ownerRestaurantId);
        } else {
            // The admin review queue handles restaurant-level ratings only.
            $query->where('ratings.ratable_type', Restaurant::class);
        }

        if (request()->filled('kind')) {
            $query->where('ratings.ratable_type', request('kind') === 'restaurant' ? Restaurant::class : FoodItem::class);
        }
        if (request()->filled('status')) {
            $this->applyDecisionFilter($query, request('status'));
        }
        if (request()->filled('order_id'))
            $query->where('ratings.order_id', request('order_id'));
        if (request()->filled('rating'))
            $query->where('ratings.rating', request('rating'));
        if (request()->filled('restaurant_id')) {
            $restaurantId = request('restaurant_id');
            $query->where(function ($q) use ($restaurantId) {
                $q->where(function ($restaurant) use ($restaurantId) {
                    $restaurant->where('ratings.ratable_type', Restaurant::class)->where('ratings.ratable_id', $restaurantId);
                })->orWhere(function ($food) use ($restaurantId) {
                    $food->where('ratings.ratable_type', FoodItem::class)->where('rated_food.restaurant_id', $restaurantId);
                });
            });
        }
        if (request()->filled('food_item_id')) {
            $query->where('ratings.ratable_type', FoodItem::class)->where('ratings.ratable_id', request('food_item_id'));
        }
        if (request()->filled('rating_id'))
            $query->where('ratings.id', request('rating_id'));
        if (request()->filled('customer_id'))
            $query->where('ratings.user_id', request('customer_id'));
        if (request()->filled('date_from'))
            $query->whereDate('ratings.created_at', '>=', request('date_from'));
        if (request()->filled('date_to'))
            $query->whereDate('ratings.created_at', '<=', request('date_to'));

        return $query;
    }

    private function applyDecisionFilter($query, string $status): void
    {
        $query->where(function ($all) use ($status) {
            $all->where(function ($restaurant) use ($status) {
                $restaurant->where('ratings.ratable_type', Restaurant::class)->where('ratings.admin_status', $status);
            })->orWhere(function ($food) use ($status) {
                $food->where('ratings.ratable_type', FoodItem::class);
                if ($status === 'approved') {
                    $food->where(function ($decision) use ($status) {
                        $decision->where('ratings.admin_status', $status)->orWhere(function ($owner) use ($status) {
                            $owner->where('ratings.admin_status', '!=', 'rejected')->where('ratings.restaurant_status', $status);
                        });
                    });
                } elseif ($status === 'rejected') {
                    $food->where(function ($decision) use ($status) {
                        $decision->where('ratings.admin_status', $status)->orWhere(function ($owner) use ($status) {
                            $owner->where('ratings.admin_status', '!=', 'approved')->where('ratings.restaurant_status', $status);
                        });
                    });
                } else {
                    $food->where('ratings.admin_status', $status)->where('ratings.restaurant_status', $status);
                }
            });
        });
    }

    public function prepareColumns()
    {
        $ownerRestaurantId = $this->getOwnerRestaurantId();
        $this->addColumn([
            'index' => 'reviewer',
            'label' => 'Customer',
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                $name = $row->customer_name ?: ($row->user_name ?: ($row->guest_name ?: ($row->guest_email ?: 'Customer')));
                $email = $row->customer_email ?: ($row->user_email ?: $row->guest_email);
                $initial = mb_strtoupper(mb_substr(trim($name), 0, 1));
                $kind = $row->user_id ? 'Customer' : 'Guest';

                return '<div class="flex min-w-[150px] items-center gap-3">'
                    . '<span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-50 text-sm font-bold text-orange-700">' . e($initial) . '</span>'
                    . '<span class="min-w-0"><span class="block truncate font-semibold text-slate-800">' . e($name) . '</span>'
                    . '<span class="mt-0.5 block text-xs text-slate-500">' . e($kind) . '</span>'
                    . ($email ? '<span class="mt-0.5 block max-w-[190px] truncate text-xs text-slate-500" title="' . e($email) . '">' . e($email) . '</span>' : '')
                    . '</span></div>';
            },
        ]);

        $this->addColumn([
            'index' => 'ratable_type',
            'label' => 'Rating type',
            'type' => 'string',
            'visibility' => false,
            'searchable' => false,
            'sortable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => false,
            'filterable_options' => $ownerRestaurantId
                ? [['label' => 'Food item', 'value' => FoodItem::class]]
                : [['label' => 'Restaurant', 'value' => Restaurant::class]],
        ]);

        $this->addColumn([
            'index' => 'entity_name',
            'label' => 'Rated item',
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                $isRestaurant = $row->ratable_type === Restaurant::class;
                $name = $row->entity_name ?: class_basename($row->ratable_type) . ' #' . $row->ratable_id;
                $type = $isRestaurant ? 'Restaurant' : (str_contains($row->ratable_type, 'FoodItem') ? 'Food item' : class_basename($row->ratable_type));
                $tone = $isRestaurant ? 'bg-orange-50 text-orange-700' : 'bg-violet-50 text-violet-700';

                return '<div class="min-w-[145px]"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ' . $tone . '">' . e($type) . '</span>'
                    . '<span class="mt-1.5 block max-w-[190px] truncate font-semibold text-slate-800" title="' . e($name) . '">' . e($name) . '</span></div>';
            },
        ]);

        $this->addColumn([
            'index' => 'order_id',
            'label' => 'Order',
            'type' => 'integer',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                if (!$row->order_id) {
                    return '<span class="text-slate-400">—</span>';
                }

                $orderNumber = $row->order_number ?: '#' . $row->order_id;
                $url = e(route('admin.orders.show', $row->order_id));

                return '<a href="' . $url . '" class="font-semibold text-orange-700 underline-offset-2 hover:text-orange-800 hover:underline" title="Open order ' . e($orderNumber) . '">'
                    . e($orderNumber) . '</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'rating',
            'label' => 'Rating',
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'allow_multiple_values' => true,
            'filterable_options' => [
                ['label' => '5 stars', 'value' => 5],
                ['label' => '4 stars', 'value' => 4],
                ['label' => '3 stars', 'value' => 3],
                ['label' => '2 stars', 'value' => 2],
                ['label' => '1 star', 'value' => 1],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $stars = str_repeat('<span class="text-amber-400">★</span>', max(0, min(5, (int) $row->rating)));
                return '<div class="min-w-[130px]"><div class="flex items-center gap-1 text-base leading-none" aria-label="' . e($row->rating) . ' out of 5 stars">'
                    . $stars . '<span class="ml-1 rounded-md bg-amber-50 px-1.5 py-1 text-xs font-bold text-amber-800">' . e(number_format((float) $row->rating, 1)) . '</span></div>'
                    . '<span class="mt-1 block text-[11px] text-slate-500">' . e($row->rating) . ' of 5</span></div>';
            },
        ]);

        $this->addColumn([
            'index' => 'review',
            'label' => 'Review',
            'type' => 'string',
            'searchable' => true,
            'sortable' => false,
            'closure' => function ($row) {
                if (!$row->review) {
                    return '<span class="text-sm italic text-slate-400">No written review</span>';
                }
                $preview = mb_strimwidth(trim($row->review), 0, 86, '…');
                return '<span class="block max-w-[250px] truncate text-sm leading-5 text-slate-600" title="' . e($row->review) . '">' . e($preview) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'workflow_actions',
            'label' => 'Decision',
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'closure' => function ($row) use ($ownerRestaurantId) {
                // Restaurant owners manage food-item reviews themselves. Show their
                // decision here; the admin queue continues to show the admin decision.
                $decision = $ownerRestaurantId && $row->ratable_type === FoodItem::class
                    ? ($row->restaurant_status ?: 'pending')
                    : ($row->admin_status ?: ($row->is_approved ? 'approved' : 'pending'));
                $tone = match ($decision) {
                    'approved' => 'bg-emerald-50 text-emerald-700',
                    'rejected' => 'bg-rose-50 text-rose-700',
                    default => 'bg-amber-50 text-amber-700',
                };
                return '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ' . $tone . '">' . e(ucfirst($decision)) . '</span>';
            },
        ]);

        $this->addFilter('status', 'ratings.status');
        $this->addFilter('created_at', 'ratings.created_at');
        $this->addFilter('order_id', 'ratings.order_id');
        $this->addFilter('id', 'ratings.id');
        $this->addFilter('ratable_type', 'ratings.ratable_type');
        $this->addFilter('approval', DB::raw("CASE WHEN ratings.ratable_type = '" . Restaurant::class . "' THEN ratings.admin_status WHEN ratings.ratable_type = '" . FoodItem::class . "' THEN CASE WHEN ratings.admin_status IN ('approved','rejected') THEN ratings.admin_status ELSE ratings.restaurant_status END ELSE CASE WHEN ratings.is_approved = 1 THEN 'approved' ELSE 'pending' END END"));
    }

    public function prepareActions()
    {
        $ownerRestaurantId = $this->getOwnerRestaurantId();
        $admin = Auth::guard('admin')->user();
        $canManage = $admin && ($admin->role?->permission_type === 'all' || $admin->hasPermission('ratings.workflow.manage'));

        $this->addAction([
            'icon' => 'visibility',
            'title' => 'View review',
            'method' => 'GET',
            'is_popup' => true,
            'url' => fn ($row) => $ownerRestaurantId
                ? route('restaurant.ratings.details', $row->id)
                : route('ratings.workflow.admin.details', $row->id),
        ]);

        if (!$ownerRestaurantId && $canManage) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete review',
                'method' => 'DELETE',
                'url' => fn ($row) => route('ratings.destroy', $row->id),
            ]);
        }
    }

    public function prepareMassActions()
    {
        $admin = Auth::guard('admin')->user();
        $canManage = $admin && ($admin->role?->permission_type === 'all' || $admin->hasPermission('ratings.workflow.manage'));

        if ($canManage) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Delete selected',
                'method' => 'POST',
                'action' => 'text-white bg-rose-600',
                'url' => 'ratings.bulk-delete',
            ]);
        }
    }

    private function getOwnerRestaurantId(): ?int
    {
        if (!$this->ownerContextResolved) {
            $this->ownerContextResolved = true;
            $admin = Auth::guard('admin')->user();
            if ($admin && app(RestaurantOwnerRoleResolver::class)->isOwner($admin)) {
                $id = Restaurant::where('admin_id', $admin->id)->value('id');
                $this->ownerRestaurantId = $id ? (int) $id : null;
            }
        }

        return $this->ownerRestaurantId;
    }

}
