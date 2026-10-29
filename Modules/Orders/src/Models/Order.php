<?php

namespace Modules\Orders\Models;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Customer;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\OrderItem;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Restaurant\Models\Restaurant;
use Modules\Payments\Models\Payments;
use Illuminate\Database\Eloquent\Model;
use Modules\Orders\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'customer_type',
        'customer_id',
        'vendor_type',
        'vendor_id',
        'admin_id',
        'order_number',
        'status',
        'order_type',
        'subtotal',
        'discount',
        'tax',
        'delivery_fee',
        'service_charge',
        'total',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'currency',
        'coupon_code',
        'payment_method',
        'payment_status',
        'delivery_address',
        'billing_address',
        'estimated_delivery_time',
        'actual_delivery_time',
        'delivery_instructions',
        'phone_number',
        'ip_address',
        'user_agent',
        'created_by',
    ];

    protected $casts = [
        'delivery_address' => 'array',
        'billing_address' => 'array',
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'cancelled_at' => 'datetime',
        'stock_restored_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Order status constants aligned to the actual workflow:
    // W = Pending, O = Order Received, P = Order Accepted, H = Preparing,
    // Z = Ready, N = Out for Delivery, D = Delivered, C = Cancelled,
    // F = Failed, R = Refunded
    const STATUS_NEW = 'O';                  // Order Received
    const STATUS_PENDING = 'W';              // Pending
    const STATUS_ACCEPTED = 'P';             // Order Accepted
    const STATUS_PROCESSING = 'H';           // Preparing
    const STATUS_READY = 'Z';               // Ready
    const STATUS_OUT_FOR_DELIVERY = 'N';    // Out for Delivery
    const STATUS_DELIVERED = 'D';            // Delivered
    const STATUS_CANCELLED = 'C';            // Cancelled
    const STATUS_FAILED = 'F';               // Failed
    const STATUS_REFUNDED = 'R';             // Refunded

    // Backward compatibility aliases for older code references
    const STATUS_INCOMPLETE = 'O';          // Legacy alias for Order Received
    const STATUS_COMPLETED = 'D';            // Legacy alias for Delivered

    // Order Type Constants
    const TYPE_DELIVERY = 'delivery';
    const TYPE_TAKEAWAY = 'takeaway';
    const TYPE_DINE_IN = 'dine_in';

    // Payment Status Constants
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_FAILED = 'failed';
    const PAYMENT_REFUNDED = 'refunded';

    // Created By Constants
    const CREATED_BY_CUSTOMER = 'customer';
    const CREATED_BY_VENDOR = 'vendor';

    /* ================= Relationships ================= */

    /**
     * Polymorphic relationship to customer (can be User or Customer)
     * Usage examples:
     * - For App\Models\User: $order->customer_type = 'App\Models\User'; $order->customer_id = $userId;
     * - For Modules\Acl\Models\Customer: $order->customer_type = 'Modules\Acl\Models\Customer'; $order->customer_id = $customerId;
     */
    public function customer()
    {
        return $this->morphTo();
    }

    /**
     * Polymorphic relationship to vendor (can be Restaurant, Store, etc.)
     * Usage examples:
     * - For Modules\Restaurant\Models\Restaurant: $order->vendor_type = 'Modules\Restaurant\Models\Restaurant'; $order->vendor_id = $restaurantId;
     */
    public function vendor()
    {
        return $this->morphTo();
    }

    /**
     * Legacy relationship for backward compatibility
     * @deprecated Use vendor() instead
     */
    public function restaurant()
    {
        return $this->vendor();
    }

    public function user()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->where('customer_type', Customer::class);
    }

    public function admin()
    {
        return $this->belongsTo(\Modules\Acl\Models\Admin::class);
    }

    public function statusRelation()
    {
        return $this->belongsTo(Status::class, 'status', 'status_code')
            ->where('type_code', 'O');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payments::class);
    }

    public function payment()
    {
        return $this->hasOne(Payments::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    /* ================= Scopes ================= */

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByOrderType($query, $type)
    {
        return $query->where('order_type', $type);
    }

    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Legacy scope for backward compatibility
     * @deprecated Use scopeByVendor() instead
     */
    public function scopeByRestaurant($query, $restaurantId)
    {
        return $this->scopeByVendor($query, $restaurantId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /* ================= Helpers ================= */

    public function getFormattedStatusAttribute()
    {
        $status = fn_get_order_status($this->status, 'O');
        return $status ? $status->name : ucfirst($this->status);
    }

    public function getFormattedOrderTypeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->order_type));
    }

    public function isCashOnDelivery(): bool
    {
        return in_array(strtolower($this->payment_method), ['cash_on_delivery', 'cod', 'cash']);
    }

    public function isOnlinePayment(): bool
    {
        return !$this->isCashOnDelivery();
    }

    public function getPaymentMethodTypeAttribute(): string
    {
        return $this->isCashOnDelivery() ? 'COD' : 'Online';
    }

    public function getDeliveryAddressWithUserAttribute()
    {
        $address = $this->delivery_address ?? [];
        $address['customer'] = $this->customer ? $this->customer->toArray() : [];

        return $this->formatAddress($address);
    }

    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            1 => 'Cash on Delivery',
            2 => 'Online'
        ];

        return $methods[$this->payment_method] ?? 'unknown';
    }

    protected function formatAddress(array $address): string
    {
        $formatted = [];

        if (!empty($address['customer']['name'])) {
            $formatted[] = $address['customer']['name'];
        }

        if (!empty($address['address_line1'])) {
            $formatted[] = $address['address_line1'];
        }

        if (!empty($address['address_line2'])) {
            $formatted[] = $address['address_line2'];
        }

        if (!empty($address['city'])) {
            $formatted[] = $address['city'];
        }

        if (!empty($address['state'])) {
            $formatted[] = $address['state'];
        }

        if (!empty($address['postal_code'])) {
            $formatted[] = $address['postal_code'];
        }

        return implode(', ', $formatted);
    }

    public function isDelivery()
    {
        return $this->order_type === self::TYPE_DELIVERY;
    }

    public function isTakeaway()
    {
        return $this->order_type === self::TYPE_TAKEAWAY;
    }

    public function isDineIn()
    {
        return $this->order_type === self::TYPE_DINE_IN;
    }

    public function isCreatedByCustomer()
    {
        return $this->created_by === self::CREATED_BY_CUSTOMER;
    }

    public function isCreatedByVendor()
    {
        return $this->created_by === self::CREATED_BY_VENDOR;
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use isCreatedByVendor() instead
     */
    public function isCreatedByRestaurant()
    {
        return $this->isCreatedByVendor();
    }

    /**
     * Create order for a User model
     */
    public static function createForUser($userId, array $orderData)
    {
        return static::create(array_merge($orderData, [
            'customer_type' => \App\Models\User::class,
            'customer_id' => $userId,
        ]));
    }

    /**
     * Create order for a Customer model
     */
    public static function createForCustomer($customerId, array $orderData)
    {
        return static::create(array_merge($orderData, [
            'customer_type' => \Modules\Acl\Models\Customer::class,
            'customer_id' => $customerId,
        ]));
    }

    public function isPaid()
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }


   
    public function canBeCancelled(): bool
    {
        $restaurant = $this->vendor;

        if ($restaurant && method_exists($restaurant, 'allowsOrderCancellation')) {
            if (!$restaurant->allowsOrderCancellation()) {
                return false;
            }

            $allowedStatuses = $restaurant->getCancellableStatuses();
            if (!empty($allowedStatuses) && is_array($allowedStatuses)) {
                return in_array($this->status, $allowedStatuses, true);
            }
        }

        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_NEW,
            self::STATUS_ACCEPTED,
            self::STATUS_PROCESSING,
        ], true);
    }

    public function getTotalItemsCount()
    {
        return $this->items->sum('quantity');
    }

    public function calculateTotal()
    {
        $itemsTotal = $this->items->sum('total_price');
        return $itemsTotal + $this->delivery_fee + $this->service_charge + $this->tax - $this->discount;
    }

    public function pendingTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id')
            ->where('status', TransactionStatus::PENDING)
            ->latest();
    }

    public function pendingTransactions()
    {
        return $this->hasMany(Transaction::class, 'order_id')
            ->where('status', TransactionStatus::PENDING)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if order can be cancelled by customer based on restaurant policy
     */
    public function isCancellableByCustomer(): array
    {
        // Load restaurant if not loaded
        if (!$this->relationLoaded('vendor')) {
            $this->load('vendor');
        }

        $restaurant = $this->vendor;

        if (!$restaurant) {
            return [
                'can_cancel' => false,
                'reason' => 'Restaurant not found',
                'reason_code' => 'RESTAURANT_NOT_FOUND'
            ];
        }

        // Check if order is already cancelled or completed
        if (in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_DELIVERED])) {
            return [
                'can_cancel' => false,
                'reason' => 'Order is already ' . strtolower($this->formatted_status),
                'reason_code' => 'ORDER_FINALIZED'
            ];
        }

        // Use restaurant's cancellation policy
        return $restaurant->canCancelOrder($this);
    }

    /**
     * Get minutes until cancellation deadline
     */
    public function getMinutesUntilCancellationDeadline(): ?int
    {
        if (!$this->relationLoaded('vendor')) {
            $this->load('vendor');
        }

        $restaurant = $this->vendor;

        if (!$restaurant || !$restaurant->allowsOrderCancellation()) {
            return null;
        }

        $timeLimit = $restaurant->getCancellationTimeLimit();
        $orderCreatedAt = \Carbon\Carbon::parse($this->created_at);
        $cancellationDeadline = $orderCreatedAt->copy()->addMinutes($timeLimit);
        $now = \Carbon\Carbon::now();

        if ($now->greaterThan($cancellationDeadline)) {
            return 0;
        }

        return $now->diffInMinutes($cancellationDeadline);
    }

    /**
     * Get cancellation deadline timestamp
     */
    public function getCancellationDeadline(): ?\Carbon\Carbon
    {
        if (!$this->relationLoaded('vendor')) {
            $this->load('vendor');
        }

        $restaurant = $this->vendor;

        if (!$restaurant || !$restaurant->allowsOrderCancellation()) {
            return null;
        }

        $timeLimit = $restaurant->getCancellationTimeLimit();
        return \Carbon\Carbon::parse($this->created_at)->addMinutes($timeLimit);
    }
    public function canDelete(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED
        ], true)
            && $this->payment_status === PaymentStatus::UNPAID->value;
    }
}
