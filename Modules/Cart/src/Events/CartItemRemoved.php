<?php

namespace Modules\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;

class CartItemRemoved
{
    use Dispatchable, SerializesModels;

    public Cart $cart;
    public array $removedItemData; // Store item data since it's deleted
    public ?int $userId;
    public ?string $sessionId;

    public function __construct(Cart $cart, array $removedItemData, ?int $userId = null, ?string $sessionId = null)
    {
        $this->cart = $cart;
        $this->removedItemData = $removedItemData;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
    }
}