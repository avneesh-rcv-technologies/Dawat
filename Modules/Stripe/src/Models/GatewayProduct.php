<?php

namespace Modules\Stripe\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayProduct extends Model
{
    protected $table = 'gateway_products';

    protected $fillable = [
        'gateway',
        'gateway_product_id',
        'gateway_price_id',
        'name',
        'amount',
        'currency',
        'billing_cycle',
        'interval_count',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'interval_count' => 'integer',
    ];

    /**
     * Find or create a cached gateway product/price.
     * Returns the gateway_price_id if found or created.
     */
    public static function findOrCreatePrice(
        string $name,
        float $amount,
        string $currency = 'usd',
        ?string $billingCycle = null,
        ?int $intervalCount = null,
        array $metadata = []
    ): ?string {
        // Try to find existing
        $existing = self::where('gateway', 'stripe')
            ->where('name', $name)
            ->where('amount', $amount)
            ->where('currency', $currency)
            ->where('billing_cycle', $billingCycle)
            ->where('interval_count', $intervalCount)
            ->first();

        if ($existing && $existing->gateway_price_id) {
            return $existing->gateway_price_id;
        }

        // If not found, return null (caller should create new one)
        return null;
    }

    /**
     * Find all cached prices for a product (by name/amount/currency).
     * Used to detect if different billing cycles exist for the same product.
     */
    public static function findAllPricesForProduct(
        string $name,
        float $amount,
        string $currency = 'usd'
    ) {
        return self::where('gateway', 'stripe')
            ->where('name', $name)
            ->where('amount', $amount)
            ->where('currency', $currency)
            ->get();
    }

    /**
     * Store a gateway product/price mapping.
     */
    public static function storePrice(
        string $gatewayProductId,
        string $gatewayPriceId,
        string $name,
        float $amount,
        string $currency = 'usd',
        ?string $billingCycle = null,
        ?int $intervalCount = null,
        array $metadata = []
    ): self {
        return self::create([
            'gateway' => 'stripe',
            'gateway_product_id' => $gatewayProductId,
            'gateway_price_id' => $gatewayPriceId,
            'name' => $name,
            'amount' => $amount,
            'currency' => $currency,
            'billing_cycle' => $billingCycle,
            'interval_count' => $intervalCount,
            'metadata' => $metadata,
        ]);
    }
}
