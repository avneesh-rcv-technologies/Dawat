<?php

namespace Modules\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Cart\Models\Cart;

class CartCleared
{
    use Dispatchable, SerializesModels;

    public Cart $cart;
    public array $clearedItemsData; // Store items data since they're deleted
    public ?int $userId;
    public ?string $sessionId;

    public function __construct(Cart $cart, array $clearedItemsData, ?int $userId = null, ?string $sessionId = null)
    {
        $this->cart = $cart;
        $this->clearedItemsData = $clearedItemsData;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
    }
}