<?php

namespace Modules\Acl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Cart\Traits\HasCart;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasCart;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status',
        'role_id',
        'guest_method',
        'number_of_guests',
        'preferred_delivery_date',
        'is_guest',
        'phone_verified',
        'phone_verified_at',
        'auth_methods',
        'primary_auth_method',
        'avatar_url',
        'email_verified',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'preferred_delivery_date' => 'datetime',
        'number_of_guests' => 'integer',
        'status' => 'boolean',
        'is_guest' => 'boolean',
        'phone_verified' => 'boolean',
        'phone_verified_at' => 'datetime',
        'auth_methods' => 'array',
        'email_verified' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id')
            ->where('guard', 'customer' ?? 'web');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role && $this->role->permission_type === 'all') {
            return true;
        }

        return in_array($permission, $this->role->permissions ?? []);
    }

    /**
     * Get all addresses for the customer.
     */
    public function addresses()
    {
        return $this->morphMany(\Modules\Countries\Models\Address::class, 'addressable');
    }

    /**
     * Get the customer's cart for a specific restaurant (uses session-based approach)
     */
    public function cart($restaurantId = null, $sessionId = null)
    {
        $sessionId = $sessionId ?: session()->getId();

        // Use the new session-based cart relationship
        $query = $this->ownedCarts()->where('session_id', $sessionId);

        if ($restaurantId) {
            $query->forRestaurant($restaurantId);
        }

        return $restaurantId ? $query->first() : $query;
    }

    /**
     * Get all orders for the customer.
     */
    public function orders()
    {
        return $this->hasMany(\Modules\Orders\Models\Order::class, 'customer_id')
            ->where('customer_type', self::class);
    }

    /**
     * Get all social accounts for the customer
     */
    public function socialAccounts()
    {
        return $this->hasMany(CustomerSocialAccount::class);
    }

    /**
     * Get social account by provider
     */
    public function socialAccount(string $provider)
    {
        return $this->socialAccounts()->where('provider', $provider)->first();
    }

    /**
     * Check if customer has a specific auth method
     */
    public function hasAuthMethod(string $method): bool
    {
        $methods = $this->auth_methods ?? [];
        return in_array($method, $methods);
    }

    /**
     * Add auth method to customer
     */
    public function addAuthMethod(string $method): void
    {
        $methods = $this->auth_methods ?? [];
        if (!in_array($method, $methods)) {
            $methods[] = $method;
            $this->update(['auth_methods' => $methods]);
        }
    }

    /**
     * Set primary auth method
     */
    public function setPrimaryAuthMethod(string $method): void
    {
        $this->update(['primary_auth_method' => $method]);
    }

    /**
     * Route notifications for SMS channel
     */
    public function routeNotificationFor($channel, $notification = null)
    {
        if ($channel === 'sms') {
            return $this->phone;
        }
        
        return null;
    }
}

