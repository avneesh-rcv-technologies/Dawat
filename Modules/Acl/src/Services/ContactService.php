<?php

namespace Modules\Acl\Services;

use Modules\Acl\Models\SupportMessage;
use Modules\Acl\Notifications\ContactMessageReceived;
use Modules\Acl\Notifications\ContactMessageConfirmation;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class ContactService
{
    /**
     * Create a contact message from guest user
     */
    public function createGuestMessage(array $data): SupportMessage
    {
        return DB::transaction(function () use ($data) {
            $message = SupportMessage::create([
                'reference_number' => SupportMessage::generateReferenceNumber(),
                'name' => $data['name'],
                'email' => $data['email'],
                'category' => $data['category'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'order_id' => $data['order_id'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status' => SupportMessage::STATUS_NEW,
            ]);

            // Send notifications
            $this->sendNotifications($message);

            return $message;
        });
    }

    /**
     * Create a contact message from authenticated customer
     */
    public function createCustomerMessage($customer, array $data): SupportMessage
    {
        return DB::transaction(function () use ($customer, $data) {
            $message = SupportMessage::create([
                'reference_number' => SupportMessage::generateReferenceNumber(),
                'name' => $customer->name,
                'email' => $customer->email,
                'customer_id' => $customer->id,
                'category' => $data['category'],
                'subject' => $data['subject'],
                'message' => $data['message'],
                'order_id' => $data['order_id'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status' => SupportMessage::STATUS_NEW,
            ]);

            // Send notifications
            $this->sendNotifications($message);

            return $message;
        });
    }

    /**
     * Get customer's message history
     */
    public function getCustomerMessages($customerId, int $perPage = 10)
    {
        return SupportMessage::byCustomer($customerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get single message by reference number
     */
    public function getMessageByReference(string $referenceNumber, $customerId = null): ?SupportMessage
    {
        $query = SupportMessage::where('reference_number', $referenceNumber);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        return $query->first();
    }

    /**
     * Send notifications for new message
     */
    protected function sendNotifications(SupportMessage $message): void
    {
        // Send notification to support team (admin email)
        $adminEmail = config('mail.support_email', config('mail.from.address'));
        Notification::route('mail', $adminEmail)
            ->notify(new ContactMessageReceived($message));

        // Send confirmation to user
        Notification::route('mail', $message->email)
            ->notify(new ContactMessageConfirmation($message));
    }

    /**
     * Get available categories
     */
    public function getCategories(): array
    {
        return collect(SupportMessage::getCategories())->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
            ];
        })->values()->toArray();
    }
}
