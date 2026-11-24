<?php

namespace Modules\Payments\Notifications;

use Modules\Payments\Models\Refund;

class RefundStatusUpdateNotification extends PaymentRefundedNotification
{
    protected ?Refund $refund = null;

    public function __construct($refund = null)
    {
        $this->refund = $refund instanceof Refund ? $refund : null;
        parent::__construct($this->refund?->payment);
    }

    public function getEventCode(): string
    {
        return 'refund_status_updated';
    }

    public function getVariables(): array
    {
        if (! $this->refund) {
            return [];
        }
        $variables = parent::getVariables();

        return array_replace($variables, [
            'refund_id' => $this->refund->id,
            'refund_number' => $this->refund->refund_number,
            'refund_amount' => fn_format_currency_amount($this->refund->refund_amount, $this->payment->currency),
            'refund_reason' => $this->refund->refund_reason ?: 'Order cancellation',
            'refund_status' => ucfirst($this->refund->refund_status),
            'refund_reference' => $this->refund->stripe_refund_id ?: 'Not recorded',
            'status_message' => match ($this->refund->refund_status) {
                'pending' => 'Your refund request is awaiting review. No refund has been recorded yet.',
                'processing' => 'Your refund is being processed. Completion has not yet been confirmed.',
                'succeeded' => 'Your refund has been recorded as completed.',
                'failed' => 'The refund attempt failed. Please contact support for assistance.',
                'rejected' => 'Your refund request was rejected. Please contact support for details.',
            },
        ]);
    }

    public function getAvailableVariables(): array
    {
        return parent::getAvailableVariables() + ['refund_id' => 'Refund request ID', 'refund_number' => 'Unique refund number', 'refund_status' => 'Current refund status',
            'refund_reference' => 'Gateway or manual refund reference', 'status_message' => 'Explanation of the refund status'];
    }
}
