<?php

namespace Modules\Payments\Notifications;

use Exception;
use Modules\Orders\Models\Transaction;
use Modules\Notifications\Contracts\Notification;
use Modules\Notifications\Models\NotificationEvent;
use Modules\Notifications\Models\NotificationChannel;
use Modules\Notifications\Models\NotificationMapping;

class TransactionNotification implements Notification
{
    public function __construct(protected $transaction = null)
    {
    }

    public function getEventCode(): string
    {
        return 'transaction_created';
    }

    public function getTemplateIdentifier(): array
    {
        $channels = [];

        try {
            $event = NotificationEvent::where('event_code', $this->getEventCode())->first();

            if (!$event) {
                return $channels;
            }

            $mappings = NotificationMapping::where('event_id', $event->id)->get();

            foreach ($mappings as $mapping) {
                $channel = NotificationChannel::find($mapping->channel_id);
                if ($channel) {
                    $channels[$channel->name] = $mapping->template_id;

                    $channels['notifiy_user'] = $mapping->notify_customer;
                    $channels['notifiy_user_admin'] = $mapping->notify_user_admin;
                    $channels['notifiy_admin'] = $mapping->notify_admin;
                    $channels['notify_technician'] = $mapping->notify_technician;
                }
            }
        } catch (Exception $e) {
            // You may log here if needed
        }

        return $channels;
    }

    public function getVariables(): array
    {
        // Handle case when called without proper transaction data (e.g., for getting available recipients)
        if (!$this->transaction instanceof Transaction) {
            return [];
        }

        return [
            'user_name' => $this->transaction->user->name ?? '',
            'user_email' => $this->transaction->user->email ?? '',
            'transaction_id' => $this->transaction->id,
            'transaction_date' => $this->transaction->created_at->format('Y-m-d H:i:s'),
            'transaction_amount' => fn_get_currency($this->transaction->amount),
            'transaction_status' => ucfirst($this->transaction->status),
            'payment_method' => $this->transaction->payment_method ?? 'N/A',
            'receipt_url' => $this->transaction->metadata['receiptUrl'] ?? '',
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            'user_name' => 'Full name of the customer',
            'user_email' => 'Customer email address',
            'transaction_id' => 'Unique ID of the transaction',
            'transaction_date' => 'Date and time when transaction was created',
            'transaction_amount' => 'Formatted transaction amount',
            'transaction_status' => 'Transaction status (e.g., Paid, Pending, Failed)',
            'payment_method' => 'Payment method used (Card, UPI, etc.)',
            'receipt_url' => 'Download link for the transaction receipt',
        ];
    }

    public function getAvailableRecipients(): array
    {
        return [
            'notify_admin' => 'Notify Admin',
            'notify_customer' => 'Notify Customer',
            'notify_user_admin' => 'Notify User Admin',
        ];
    }

    public function getRecipientTypeMapping(): array
    {
        return [
            'notify_admin' => 'admin',
            'notify_customer' => 'notifiable',
            'notify_user_admin' => 'notifiable'
        ];
    }

    public function getRecipientClassMapping(): array
    {
        return [
            'notify_admin' => \Modules\Acl\Models\Admin::class,
            'notify_customer' => \App\Models\User::class,
            'notify_user_admin' => \Modules\Acl\Models\Admin::class
        ];
    }

    public function toArray(): array
    {
        return [
            'event_code' => $this->getEventCode(),
            'template' => $this->getTemplateIdentifier(),
            'variables' => $this->getVariables(),
            'available_variables' => $this->getAvailableVariables(),
        ];
    }
}
