<?php

namespace Modules\Acl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Orders\Models\Order;

class SupportMessage extends Model
{
    use HasFactory;

    protected $table = 'support_messages';

    protected $fillable = [
        'reference_number',
        'name',
        'email',
        'category',
        'subject',
        'message',
        'customer_id',
        'order_id',
        'status',
        'ip_address',
        'user_agent',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';
    const STATUS_REPLIED = 'replied';
    const STATUS_CLOSED = 'closed';

    // Category constants
    const CATEGORY_GENERAL = 'general_inquiry';
    const CATEGORY_ORDER = 'order_issue';
    const CATEGORY_DELIVERY = 'delivery_issue';
    const CATEGORY_PAYMENT = 'payment_issue';
    const CATEGORY_ACCOUNT = 'account_issue';
    const CATEGORY_TECHNICAL = 'technical_issue';
    const CATEGORY_FEEDBACK = 'feedback';
    const CATEGORY_OTHER = 'other';

    /* ============================
     | Relationships
     |============================ */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /* ============================
     | Scopes
     |============================ */

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeUnreplied($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, self::STATUS_READ]);
    }

    /* ============================
     | Helpers
     |============================ */

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isReplied(): bool
    {
        return $this->status === self::STATUS_REPLIED;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function markAsRead(): void
    {
        if ($this->status === self::STATUS_NEW) {
            $this->update(['status' => self::STATUS_READ]);
        }
    }

    public function markAsReplied(): void
    {
        $this->update([
            'status' => self::STATUS_REPLIED,
            'replied_at' => now(),
        ]);
    }

    public function markAsClosed(): void
    {
        $this->update(['status' => self::STATUS_CLOSED]);
    }

    public static function generateReferenceNumber(): string
    {
        do {
            $number = 'MSG-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('reference_number', $number)->exists());

        return $number;
    }

    // public static function getCategories(): array
    // {
    //     return [
    //         self::CATEGORY_GENERAL => 'General Inquiry',
    //         self::CATEGORY_ORDER => 'Order Issue',
    //         self::CATEGORY_DELIVERY => 'Delivery Issue',
    //         self::CATEGORY_PAYMENT => 'Payment Issue',
    //         self::CATEGORY_ACCOUNT => 'Account Issue',
    //         self::CATEGORY_TECHNICAL => 'Technical Issue',
    //         self::CATEGORY_FEEDBACK => 'Feedback',
    //         self::CATEGORY_OTHER => 'Other',
    //     ];
    // }

    public function getCategoryLabelAttribute(): string
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }
}
