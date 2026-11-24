<?php

namespace Modules\Cart\Services;

use Illuminate\Validation\ValidationException;
use Modules\FoodManagements\Models\FoodItem;
use Modules\FoodManagements\Models\FoodItemServingOption;
use Modules\FoodManagements\Services\ServingOptionConfiguration;

class ServingOptionCartPricing
{
    public function __construct(private ServingOptionConfiguration $configuration) {}

    public function calculate(FoodItem $food, FoodItemServingOption $option, int $quantity, ?int $guests): array
    {
        $type = $this->configuration->all()['serve_types'][$option->serve_type] ?? null;
        if (! $type) {
            throw ValidationException::withMessages(['serving_option_id' => 'This serving option is no longer offered. Please choose another option.']);
        }

        $perPerson = $type['pricing_basis'] === 'per_person';
        if ($perPerson) {
            if ($quantity !== 1) {
                throw ValidationException::withMessages(['quantity' => 'For per-person catering, set quantity to 1 and enter the number of guests in guest_count.']);
            }
            $minimum = max(1, (int) $option->min_guests);
            $maximum = $option->max_guests;
            if ($guests === null) {
                $range = $maximum !== null ? "$minimum to $maximum guests" : "at least $minimum guests";
                throw ValidationException::withMessages(['guest_count' => "Please enter the number of guests for this serving option ($range)."]);
            }
            if ($guests < $minimum) {
                throw ValidationException::withMessages(['guest_count' => "This serving option requires at least $minimum guests. Please increase the guest count."]);
            }
            if ($maximum !== null && $guests > $maximum) {
                throw ValidationException::withMessages(['guest_count' => "This serving option supports up to $maximum guests. Please reduce the guest count."]);
            }
        }
        if (! $perPerson && $guests !== null) {
            throw ValidationException::withMessages(['guest_count' => 'This serving option is priced per order. Please remove the guest count.']);
        }

        // Work in cents; round tax once on the line subtotal.
        $regularCents = (int) round((float) $option->price * 100);
        $effectiveCents = (int) round((float) ($option->discount_price ?? $option->price) * 100);
        $taxBasisPoints = (int) round((float) $food->tax_percentage * 100);
        if ($regularCents < 0 || $effectiveCents < 0 || $effectiveCents > $regularCents
            || $taxBasisPoints < 0 || $taxBasisPoints > 10000) {
            throw ValidationException::withMessages(['serving_option_id' => 'Pricing for this serving option is unavailable. Please choose another option.']);
        }
        $guestCount = $perPerson ? $guests : 1;
        $units = $perPerson ? $guestCount : $quantity;
        // Guard before multiplying so unusually large selections cannot overflow storage.
        if ($units > intdiv(9999999999, max(1, $regularCents))) {
            throw ValidationException::withMessages(['quantity' => 'This order amount is too large. Please reduce the quantity or guest count.']);
        }
        $unitCents = $effectiveCents * $guestCount;
        $subtotalCents = $effectiveCents * $units;
        $taxCents = (int) round($subtotalCents * $taxBasisPoints / 10000);
        $unitTaxCents = (int) round($unitCents * $taxBasisPoints / 10000);
        if ($subtotalCents + $taxCents > 9999999999) {
            throw ValidationException::withMessages(['quantity' => 'This order amount is too large. Please reduce the quantity or guest count.']);
        }

        $discountCents = ($regularCents - $effectiveCents) * $units;
        $price = $effectiveCents / 100;
        $perPersonPrice = $perPerson ? $price : ($option->serves ? round($price / $option->serves, 2) : null);
        $size = $option->quantity !== null && $option->unit
            ? rtrim(rtrim($option->quantity, '0'), '.').' '.$option->unit : null;
        $subtitle = $perPerson ? 'Per guest' : implode(' • ', array_filter([
            $option->serves ? 'Serves '.$option->serves : null, $size,
        ]));
        $display = [
            'title' => $option->serving_name ?: $type['label'],
            'subtitle' => $subtitle ?: 'Price per serving',
            'price_label' => $option->discount_price !== null ? 'Special offer' : ($perPerson ? 'Per guest' : ($option->serve_type === 'package' ? 'Catering order' : 'Price')),
            'unit_label' => $perPerson ? 'Per guest' : ($option->serves ? 'Serves '.$option->serves : ($size ?: 'Each')),
            'unit_price_text' => $this->money($price),
            'per_person_price' => $perPersonPrice,
        ];

        return [
            'serving_option_id' => $option->id,
            'serve_type' => $option->serve_type,
            'serving_name' => $option->serving_name,
            'quantity' => $quantity,
            'guest_count' => $guestCount,
            'unit_price' => $unitCents / 100,
            'unit_display_price' => ($unitCents + $unitTaxCents) / 100,
            'unit_tax_amount' => $unitTaxCents / 100,
            'total_price' => $subtotalCents / 100,
            'total_tax_amount' => $taxCents / 100,
            'total_display_price' => ($subtotalCents + $taxCents) / 100,
            'subtotal' => $subtotalCents / 100,
            'tax_amount' => $taxCents / 100,
            'final_price' => ($subtotalCents + $taxCents) / 100,
            // Amount saved on the entire line, before tax.
            'discount_price' => $discountCents / 100,
            'is_tax_inclusive' => false,
            'tax_breakdown' => ['item_tax' => [
                'name' => 'Item tax', 'rate' => (float) $food->tax_percentage, 'amount' => $taxCents / 100,
            ]],
            'display_title' => $display['title'],
            'display_subtitle' => $display['subtitle'],
            'display_price_label' => $display['price_label'],
            'display_unit_label' => $display['unit_label'],
            'display_unit_price_text' => $display['unit_price_text'],
            'display_per_person_price' => $perPersonPrice,
            'selected_option_snapshot' => [
                ...$option->only(['id', 'food_item_id', 'serve_type', 'serving_name', 'quantity', 'unit',
                    'serves', 'min_guests', 'max_guests', 'price', 'discount_price', 'is_available']),
                'item_name' => $food->name,
                'currency' => config('cart.default_currency', 'USD'),
                'pricing_basis' => $type['pricing_basis'],
                'effective_price' => number_format($price, 2, '.', ''),
                'tax_percentage' => $food->tax_percentage,
                'pricing_version' => 2,
            ],
        ];
    }

    public function money(float $amount, ?string $currency = null): string
    {
        $currency ??= config('cart.default_currency', 'USD');
        $symbol = match ($currency) {
            'USD' => '$', 'INR' => '₹', 'EUR' => '€', 'GBP' => '£',
            default => $currency.' ',
        };

        return $symbol.number_format($amount, 2, '.', '');
    }
}
