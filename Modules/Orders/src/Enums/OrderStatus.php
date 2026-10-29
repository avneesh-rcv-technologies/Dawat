<?php

namespace Modules\Orders\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case RECEIVED = 'received';
    case CONFIRMED = 'confirmed';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::RECEIVED => 'Order Received',
            self::CONFIRMED => 'Order Accepted',
            self::PREPARING => 'Preparing',
            self::READY => 'Ready',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::RECEIVED => 'info',
            self::CONFIRMED => 'info',
            self::PREPARING => 'primary',
            self::READY => 'success',
            self::OUT_FOR_DELIVERY => 'info',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
            self::FAILED => 'danger',
            self::REFUNDED => 'info',
        };
    }

    public function code(): string
    {
        return match($this) {
            self::PENDING => 'W',
            self::RECEIVED => 'O',
            self::CONFIRMED => 'P',
            self::PREPARING => 'H',
            self::READY => 'Z',
            self::OUT_FOR_DELIVERY => 'N',
            self::DELIVERED => 'D',
            self::CANCELLED => 'C',
            self::FAILED => 'F',
            self::REFUNDED => 'R',
        };
    }

    public static function fromCode(string $code): ?self
    {
        foreach (self::cases() as $status) {
            if ($status->code() === $code) {
                return $status;
            }
        }

        return null;
    }

    public static function workflow(string $orderType = 'delivery'): array
    {
        $statuses = self::getActiveStatuses();

        if (in_array($orderType, ['takeaway', 'dine_in'], true)) {
            $statuses = array_values(array_filter($statuses, fn (self $status) => $status !== self::OUT_FOR_DELIVERY));
        }

        return [...$statuses, self::DELIVERED];
    }

    public function allowedTransitions(string $orderType = 'delivery'): array
    {
        if (in_array($this, [self::DELIVERED, self::CANCELLED, self::FAILED, self::REFUNDED], true)) {
            return [];
        }

        $workflow = self::workflow($orderType);
        $index = array_search($this, $workflow, true);
        $next = $index === false ? [] : [$workflow[$index + 1]];

        return [...$next, self::CANCELLED, self::FAILED, self::REFUNDED];
    }

    public function canTransitionTo(OrderStatus $status, string $orderType = 'delivery'): bool
    {
        return in_array($status, $this->allowedTransitions($orderType), true);
    }

    public static function getActiveStatuses(): array
    {
        return [
            self::PENDING,
            self::RECEIVED,
            self::CONFIRMED,
            self::PREPARING,
            self::READY,
            self::OUT_FOR_DELIVERY
        ];
    }
}
