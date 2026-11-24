<?php

namespace Modules\Cart\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Cart\Services\ServingOptionCartPricing;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $snapshot = $this->selected_option_snapshot;
        $legacy = $snapshot === null;
        $currency = $snapshot['currency'] ?? config('cart.default_currency', 'USD');
        $pricing = app(ServingOptionCartPricing::class);
        $perPerson = ($snapshot['pricing_basis'] ?? null) === 'per_person';
        $serves = $perPerson ? $this->guest_count
            : (isset($snapshot['serves']) ? $snapshot['serves'] * $this->quantity : null);
        $unitPrice = (float) ($snapshot['effective_price'] ?? $this->unit_price);
        $taxRate = isset($snapshot['tax_percentage']) ? (float) $snapshot['tax_percentage'] : null;

        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'item_name' => $snapshot['item_name'] ?? $this->item?->name ?? 'Unavailable item',
            'image' => $this->item?->image['url'] ?? null,
            'serving_option_id' => $this->serving_option_id,
            'serving_option' => $snapshot === null ? null : [
                ...array_intersect_key($snapshot, array_flip([
                    'serve_type', 'serving_name', 'quantity', 'unit', 'serves', 'min_guests', 'max_guests',
                ])),
                'pricing_description' => $perPerson
                    ? 'Price is for one guest. Portion size per guest is not specified.'
                    : 'Price is for one selected serving option.',
            ],
            'quantity' => $this->quantity,
            'delivery_fee' => $this->delivery_fee,
            'estimated_delivery_minutes' => $this->estimated_delivery_minutes,
            'estimated_delivery_at' => $this->estimated_delivery_at,
            'guest_count' => $perPerson ? $this->guest_count : null,
            'total_serves' => $serves,
            'note' => $this->note ?? $this->special_instructions,
            'requires_serving_selection' => $legacy,
            'is_available' => (bool) ($this->item?->is_active && $this->item?->is_available
                && $this->item?->in_stock && ($legacy || ($this->servingOption?->is_available
                    && $this->servingOption?->food_item_id === $this->item_id))),
            'pricing' => [
                'currency' => $currency,
                'pricing_basis' => $snapshot['pricing_basis'] ?? 'legacy',
                'price_unit' => $perPerson ? 'guest' : 'option',
                'billable_units' => $perPerson ? $this->guest_count : $this->quantity,
                'regular_unit_price' => $snapshot['price'] ?? $this->unit_price,
                'discounted_unit_price' => $snapshot['discount_price'] ?? null,
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'unit_price_including_tax' => $legacy ? $this->unit_display_price
                    : number_format($unitPrice + round($unitPrice * $taxRate / 100, 2), 2, '.', ''),
                'is_tax_inclusive' => $legacy ? $this->is_tax_inclusive : false,
                'tax_percentage' => $snapshot['tax_percentage'] ?? null,
                'subtotal_before_discount' => number_format((float) $this->total_price + ($legacy ? 0 : (float) $this->discount_price), 2, '.', ''),
                'subtotal' => $this->total_price,
                'tax_amount' => $this->total_tax_amount,
                'final_price' => $this->total_display_price,
                'discount_amount' => $legacy ? '0.00' : $this->discount_price,
            ],
            'display' => [
                'title' => $this->display_title ?? $this->item?->name ?? 'Unavailable item',
                'subtitle' => $this->display_subtitle,
                'price_label' => $this->display_price_label,
                'unit_label' => $this->display_unit_label,
                'unit_price_text' => $this->display_unit_price_text ?? $pricing->money((float) $this->unit_price, $currency),
                'unit_price_tax_label' => $this->is_tax_inclusive ? 'Includes tax' : 'Excludes tax',
                'quantity_label' => $perPerson
                    ? $this->guest_count.' guests'
                    : $this->quantity.' × '.($this->serving_name ?: 'item'),
                'total_serves_label' => $serves !== null ? 'Serves '.$serves : null,
                'subtotal_text' => $pricing->money((float) $this->total_price, $currency),
                'tax_text' => $pricing->money((float) $this->total_tax_amount, $currency),
                'total_text' => $pricing->money((float) $this->total_display_price, $currency),
            ],
        ];
    }
}
