<?php

namespace Modules\Orders\Listeners;

use Modules\Core\Events\RegisterGlobalSearch;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\Transaction;
use Modules\Restaurant\Models\Restaurant;

class GlobalSearchEvent
{
    /**
     * Handle the event.
     */
    public function handle(RegisterGlobalSearch $event): void
    {
        $query = (string) $event->query;
        $moduleFilter = $event->module;

        $currentAdmin = auth('admin')->user();
        $restaurant = null;

        if ($currentAdmin) {
            $restaurant = Restaurant::where('admin_id', $currentAdmin->id)->first();
        }

        // Only register preferences if user has access
        // Restaurant admin: always show these options
        // Super admin: always show these options
        $event->addSearchPreference('orders', 'Orders');
        $event->addSearchPreference('transactions', 'Transactions');

        if (strlen($query) < 2) {
            return;
        }

        /**
         * -----------------------------
         * ORDER SEARCH
         * -----------------------------
         */
        if ($moduleFilter === 'all' || $moduleFilter === 'orders') {

            $ordersQuery = Order::query();

            // Restaurant admin ke liye sirf uske orders (using vendor_id for polymorphic relationship)
            if ($restaurant) {
                $ordersQuery->where('vendor_type', Restaurant::class)
                    ->where('vendor_id', $restaurant->id);
            }

            $orders = $ordersQuery
                ->where('order_number', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($orders as $order) {
                $event->addResult([
                    'id' => $order->id,
                    'title' => $order->order_number,
                    'url' => '/orders?filters[order_number]=' . urlencode($order->order_number),
                    'type' => 'order',
                    'module' => 'Orders',
                    'module_name' => 'Orders',
                ]);
            }
        }

        /**
         * -----------------------------
         * TRANSACTION SEARCH
         * -----------------------------
         */
        if ($moduleFilter === 'all' || $moduleFilter === 'transactions') {

            $transactionsQuery = Transaction::query();

            // Restaurant admin ke liye sirf uske transactions (through order's vendor relationship)
            if ($restaurant) {
                $transactionsQuery->whereHas('order', function ($q) use ($restaurant) {
                    $q->where('vendor_type', Restaurant::class)
                      ->where('vendor_id', $restaurant->id);
                });
            }

            $transactions = $transactionsQuery
                ->where('transaction_number', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($transactions as $transaction) {
                $event->addResult([
                    'id' => $transaction->id,
                    'title' => $transaction->transaction_number,
                    'url' => '/admin/transactions?filters[transaction_number]=' . urlencode($transaction->transaction_number),
                    'type' => 'transaction',
                    'module' => 'Orders',
                    'module_name' => 'Orders',
                ]);
            }
        }
    }
}
