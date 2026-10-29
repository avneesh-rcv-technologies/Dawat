<?php

namespace Modules\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;

class CartItemAdded
{
    use Dispatchable, SerializesModels;

    public Cart $cart;
    public CartItem $cartItem;
    public ?int $userId;
    public ?string $sessionId;

    public function __construct(Cart $cart, CartItem $cartItem, ?int $userId = null, ?string $sessionId = null)
    {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
    }
}