<?php

namespace Modules\Cart\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Cart\Models\Cart;
use Modules\Cart\Models\CartItem;

class CartItemUpdated
{
    use Dispatchable, SerializesModels;

    public Cart $cart;
    public CartItem $cartItem;
    public int $oldQuantity;
    public int $newQuantity;
    public ?int $userId;
    public ?string $sessionId;

    public function __construct(
        Cart $cart, 
        CartItem $cartItem, 
        int $oldQuantity, 
        int $newQuantity,
        ?int $userId = null, 
        ?string $sessionId = null
    ) {
        $this->cart = $cart;
        $this->cartItem = $cartItem;
        $this->oldQuantity = $oldQuantity;
        $this->newQuantity = $newQuantity;
        $this->userId = $userId;
        $this->sessionId = $sessionId;
    }
}