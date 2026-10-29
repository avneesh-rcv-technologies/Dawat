<?php

namespace Modules\Acl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GuestCustomer extends Customer
{
    use HasFactory;

    protected $table = 'customers';

    /**
     * @deprecated Use Customer model with is_guest = true instead
     * Guest customers are now created via /api/guest/generate-token endpoint
     * and authenticated using Sanctum tokens
     */
    public static function getOrCreateBySessionId(string $sessionId): self
    {
        throw new \Exception('GuestCustomer::getOrCreateBySessionId is deprecated. Use /api/guest/generate-token endpoint instead.');
    }

    /**
     * @deprecated Use Customer model with is_guest = true instead
     */
    public function isGuest(): bool
    {
        return $this->is_guest === true;
    }

    /**
     * @deprecated Use Customer model with is_guest = true instead
     */
    public static function getBySessionId(string $sessionId): ?self
    {
        throw new \Exception('GuestCustomer::getBySessionId is deprecated. Use Sanctum token authentication instead.');
    }

    /**
     * Scope to get only guest customers
     */
    public function scopeGuests($query)
    {
        return $query->where('is_guest', true);
    }

    /**
     * Scope to get only authenticated customers
     */
    public function scopeAuthenticated($query)
    {
        return $query->where('is_guest', false);
    }
}
