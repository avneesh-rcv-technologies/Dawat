<?php

namespace Modules\Orders\Support;

class CommissionRefundAmounts
{
    public static function cents($amount): int
    {
        if (! preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', (string) ($amount ?? 0), $parts)) {
            throw new \InvalidArgumentException('Money must have at most two decimal places.');
        }

        return ($parts[1] === '-' ? -1 : 1) * ((int) $parts[2] * 100 + (int) str_pad($parts[3] ?? '', 2, '0'));
    }

    public static function money(int $cents): string
    {
        return ($cents < 0 ? '-' : '').intdiv(abs($cents), 100).'.'.str_pad((string) (abs($cents) % 100), 2, '0', STR_PAD_LEFT);
    }

    /** Original order/fee/share are immutable; allocate cumulative successful refunds in cents. */
    public static function calculate($orderAmount, $percentage, $originalFee, $originalRestaurant, $refunded): array
    {
        $order = self::cents($orderAmount);
        $rate = self::cents($percentage);
        $fee = self::cents($originalFee);
        $share = self::cents($originalRestaurant);
        $total = self::cents($refunded);
        if ($order <= 0 || $rate < 0 || $rate > 10000 || $fee < 0 || $share < 0 || $fee + $share !== $order || $total < 0) {
            throw new \InvalidArgumentException('Invalid original commission snapshot or refund amount.');
        }
        // An over-captured payment cannot reverse more than the original commission entitlement.
        $basis = min($order, $total);
        $retained = min($fee, intdiv(($order - $basis) * $rate + 5000, 10000));
        $reversed = $fee - $retained;
        $deduction = $basis - $reversed;

        return ['refunded_amount' => self::money($total), 'commission_reversed_amount' => self::money($reversed),
            'restaurant_refund_deduction' => self::money($deduction), 'final_payable_amount' => self::money($share - $deduction)];
    }
}
