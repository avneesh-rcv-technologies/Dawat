@php
    $cards = [
        ['label' => 'Total records', 'field' => null, 'icon' => 'receipt_long', 'background' => '#eff6ff', 'color' => '#2563eb', 'help' => 'Matching commission records'],
        ['label' => 'Total order value', 'field' => 'order_amount', 'icon' => 'shopping_bag', 'background' => '#fff7ed', 'color' => '#ea580c', 'help' => 'Commissionable order amount'],
        ['label' => 'Effective commission', 'field' => 'platform_commission_amount', 'icon' => 'percent', 'background' => '#f5f3ff', 'color' => '#7c3aed', 'help' => 'After successful refund reversals'],
        ['label' => 'Restaurant payable', 'field' => 'restaurant_amount', 'icon' => 'account_balance_wallet', 'background' => '#ecfdf5', 'color' => '#059669', 'help' => 'After commission and successful refunds'],
        ['label' => 'Pending commission', 'field' => 'pending_amount', 'icon' => 'schedule', 'background' => '#fffbeb', 'color' => '#d97706', 'help' => number_format($summary['pending_records']) . ' awaiting admin processing'],
    ];
@endphp
@include('backend::partials.kpi-cards', ['overviewLabel' => 'Commission overview'])
