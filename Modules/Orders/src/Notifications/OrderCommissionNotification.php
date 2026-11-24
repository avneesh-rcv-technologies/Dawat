<?php

namespace Modules\Orders\Notifications;

use Modules\Acl\Models\Admin;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Support\NotificationMappingLoader;
use Modules\Orders\Models\OrderCommission;

class OrderCommissionNotification implements Notification
{
    public function __construct(private array $data = []) {}

    public function getEventCode(): string
    {
        return 'order_commission_updated';
    }

    public function getTemplateIdentifier(): array
    {
        return NotificationMappingLoader::forEventCode($this->getEventCode());
    }

    public function getVariables(): array
    {
        $commission = $this->data['commission'] ?? null;
        if (! $commission instanceof OrderCommission) {
            return [];
        }
        $money = fn ($amount) => $commission->currency.' '.number_format((float) $amount, 2);

        return [
            'order_number' => e($commission->order?->order_number ?? ''),
            'restaurant_name' => e($commission->restaurant?->name ?? 'Restaurant'),
            'order_amount' => $money($commission->order_amount),
            'commission_percentage' => number_format((float) $commission->commission_percentage, 2),
            'commission_amount' => $money($commission->effective_commission),
            'restaurant_amount' => $money($commission->final_payable_amount),
            'original_commission_amount' => $money($commission->platform_commission_amount),
            'original_restaurant_amount' => $money($commission->restaurant_amount),
            'refunded_amount' => $money($commission->refunded_amount),
            'commission_reversed_amount' => $money($commission->commission_reversed_amount),
            'commission_status' => ucfirst(str_replace('_', ' ', $commission->status)),
            'commission_message' => ($this->data['event'] ?? '') === 'created'
                ? 'The accepted platform commission has been calculated for this paid order and is pending admin processing.'
                : match ($commission->status) {
                    'processed' => 'The commission fee has been marked processed successfully.',
                    'cancelled' => 'The order was fully refunded; no commission or unpaid restaurant payout remains due.',
                    'on_hold' => 'The cancelled order commission is on hold for refund review.',
                    'failed' => 'Commission processing has been marked failed and needs review.',
                    default => 'The commission record is pending processing.',
                },
            'processed_at' => $commission->processed_at?->format('d M Y, H:i') ?? 'Pending',
            'commission_link' => route('admin.order-commissions.show', $commission->id),
        ];
    }

    public function getAvailableVariables(): array
    {
        return ['order_number' => 'Order number', 'restaurant_name' => 'Restaurant name',
            'order_amount' => 'Commissionable order total', 'commission_percentage' => 'Accepted rate snapshot',
            'commission_amount' => 'Effective Dawat commission after refunds', 'restaurant_amount' => 'Restaurant payable after refunds',
            'original_commission_amount' => 'Original commission snapshot', 'original_restaurant_amount' => 'Original restaurant share snapshot',
            'refunded_amount' => 'Successful customer refunds', 'commission_reversed_amount' => 'Commission reversed',
            'commission_status' => 'Commission status', 'commission_message' => 'Status explanation',
            'processed_at' => 'Processing time', 'commission_link' => 'Record URL'];
    }

    public function getAvailableRecipients(): array
    {
        return ['notify_restaurant' => 'Restaurant owner', 'notify_admin' => 'Platform admin'];
    }

    public function getRecipientTypeMapping(): array
    {
        return ['notify_restaurant' => 'notifiable', 'notify_admin' => 'admin'];
    }

    public function getRecipientClassMapping(): array
    {
        return ['notify_restaurant' => Admin::class, 'notify_admin' => Admin::class];
    }

    public function toArray(): array
    {
        return ['event_code' => $this->getEventCode(), 'variables' => $this->getVariables(), 'template' => $this->getTemplateIdentifier()];
    }
}
