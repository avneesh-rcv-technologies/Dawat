@extends('backend::layouts.app')

@section('title', 'Subscription Details')

@section('breadcrumbs')
    @php
        $label =
            'Subscription #' .
            $subscription->id .
            ' / ' .
            $subscription->plan->name .
            ' / ' .
            $subscription->created_at->format('F d, Y');
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Subscriptions', 'url' => route('subscriptions.index')],
            ['label' => $label, 'url' => null],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$label" />
@endsection

@section('content')

    <!-- Status Badge and Info Header -->
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Subscription #{{ $subscription->id }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Created on {{ $subscription->created_at->format('F d, Y \a\t h:i A') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-800',
                        'trialing' => 'bg-blue-100 text-blue-800',
                        'past_due' => 'bg-red-100 text-red-800',
                        'canceled' => 'bg-gray-100 text-gray-800',
                        'paused' => 'bg-yellow-100 text-yellow-800',
                        'expired' => 'bg-red-100 text-red-800',
                    ];
                    $statusColor = $statusColors[$subscription->status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                    {{ ucfirst($subscription->status) }}
                </span>
                @if ($subscription->cancel_at_period_end)
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                        <span class="material-icons-outlined text-sm mr-1">schedule</span>
                        Canceling at Period End
                    </span>
                @endif

                <!-- Action Buttons in Header -->
                <div class="flex items-center gap-1 ml-4 pl-4 border-l border-gray-200">
                    <!-- Edit Button -->
                    @if (Route::has('subscriptions.edit') && bouncer(config($guard))->hasPermission('subscriptions.update'))
                        <a href="{{ route('subscriptions.edit', $subscription->id) }}"
                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit Subscription">
                            <span class="material-icons-outlined">edit</span>
                        </a>
                    @endif

                    <!-- Pause/Resume Button -->
                    @if ($subscription->status !== 'paused' && bouncer(config($guard))->hasPermission('subscriptions.pause'))
                        <button onclick="openDeleteModal('{{ route('subscriptions.pause', $subscription->id) }}')"
                            class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition" title="Pause Subscription">
                            <span class="material-icons-outlined">pause_circle</span>
                        </button>
                    @elseif($subscription->status === 'paused' && bouncer(config($guard))->hasPermission('subscriptions.resume'))
                        <button onclick="openDeleteModal('{{ route('subscriptions.resume', $subscription->id) }}')"
                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Resume Subscription">
                            <span class="material-icons-outlined">play_circle</span>
                        </button>
                    @endif

                    <!-- Cancel Button -->
                    @if (
                        $subscription->isActive() &&
                            !$subscription->cancel_at_period_end &&
                            bouncer(config($guard))->hasPermission('subscriptions.cancel'))
                        <button onclick="openDeleteModal('{{ route('subscriptions.cancel', $subscription->id) }}')"
                            class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg transition"
                            title="Cancel Subscription">
                            <span class="material-icons-outlined">cancel</span>
                        </button>
                    @elseif(
                        $subscription->isCanceled() &&
                            !$subscription->hasEnded() &&
                            bouncer(config($guard))->hasPermission('subscriptions.resume'))
                        <button onclick="openDeleteModal('{{ route('subscriptions.resume', $subscription->id) }}')"
                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition"
                            title="Reactivate Subscription">
                            <span class="material-icons-outlined">replay</span>
                        </button>
                    @endif

                    <!-- Send Notification Button -->
                    @if (bouncer(config($guard))->hasPermission('subscriptions.sendNotification'))
                    <x-modal buttonText="<span class='material-icons-outlined'>notifications</span>"
                        modalTitle="Send Notification" id="notification-modal-{{ $subscription->id }}"
                        ajaxUrl="{{ route('subscriptions.sendNotification', $subscription->id) }}"
                        color="text-purple-600 hover:bg-purple-50 p-2 rounded-lg transition" modalSize="lg" />
                    @endif
                    <!-- Delete Button -->
                    @if (bouncer(config($guard))->hasPermission('subscriptions.destroy'))
                        <button onclick="openDeleteModal('{{ route('subscriptions.destroy', $subscription->id) }}')"
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Subscription">
                            <span class="material-icons-outlined">delete</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Plan Information -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                <span class="material-icons-outlined mr-2">shopping_bag</span>
                Plan Information
            </h3>
            <dl class="space-y-3">
                @if($subscription->subscriptionProduct)
                <div class="mb-3 pb-3 border-b border-gray-300 dark:border-gray-600">
                    <dt class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-2">Product Purchase</dt>
                    <dd class="text-xs text-gray-700 dark:text-gray-300 space-y-1">
                        <div><span class="font-medium">Product Type:</span> {{ class_basename($subscription->subscriptionProduct->product_type) }}</div>
                        <div><span class="font-medium">Product ID:</span> #{{ $subscription->subscriptionProduct->product_id }}</div>
                        <div><span class="font-medium">Purchase Price:</span> {{ $subscription->subscriptionProduct->formatted_price }}</div>
                    </dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Plan Name</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-semibold">
                        {{ $subscription->plan->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Price</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                        <span class="text-lg font-bold">{{ $subscription->plan->formatted_price }}</span> /
                        {{ $subscription->plan->billing_cycle }}
                    </dd>
                </div>
                @if ($subscription->plan->trial_days > 0)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Trial Period</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $subscription->plan->trial_days }}
                            days</dd>
                    </div>
                @endif
                @if ($subscription->plan->description)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Description</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $subscription->plan->description }}
                        </dd>
                    </div>
                @endif
                @if (!empty($subscription->plan->features))
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Plan Features</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($subscription->plan->features as $feature)
                                    <li>{{ $feature }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Subscriber Information -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                <span class="material-icons-outlined mr-2">person</span>
                Subscriber Information
            </h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Subscriber Type</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                        {{ class_basename($subscription->subscribable_type) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Subscriber ID</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-mono">
                        #{{ $subscription->subscribable_id }}
                    </dd>
                </div>
                @if ($subscription->subscribable)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Subscriber Name</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-semibold">
                            {{ $subscription->subscribable->name ?? 'N/A' }}</dd>
                    </div>
                    @if (isset($subscription->subscribable->email))
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                                <a href="mailto:{{ $subscription->subscribable->email }}"
                                    class="text-blue-600 hover:underline">
                                    {{ $subscription->subscribable->email }}
                                </a>
                            </dd>
                        </div>
                    @endif
                @endif
            </dl>
        </div>

        <!-- Subscription Dates -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                <span class="material-icons-outlined mr-2">event</span>
                Subscription Dates
            </h3>
            <dl class="space-y-3">
                @if ($subscription->start_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Start Date</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                            {{ $subscription->start_date->format('F d, Y') }}</dd>
                    </div>
                @endif
                @if ($subscription->trial_ends_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Trial Ends At</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                            {{ $subscription->trial_ends_at->format('F d, Y') }}
                            @if ($subscription->onTrial())
                                <span class="ml-2 text-xs text-blue-600">({{ $subscription->trialDaysRemaining() }}
                                    days remaining)</span>
                            @endif
                        </dd>
                    </div>
                @endif
                @if ($subscription->current_period_start)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Current Period Start</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                            {{ $subscription->current_period_start->format('F d, Y') }}</dd>
                    </div>
                @endif
                @if ($subscription->current_period_end)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Current Period End</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                            {{ $subscription->current_period_end->format('F d, Y') }}
                            <span class="ml-2 text-xs text-gray-600">({{ $subscription->daysRemaining() }} days
                                remaining)</span>
                        </dd>
                    </div>
                @endif
                @if ($subscription->canceled_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Canceled At</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                            {{ $subscription->canceled_at->format('F d, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Payment Information -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                <span class="material-icons-outlined mr-2">payment</span>
                Payment Information
            </h3>
            <dl class="space-y-3">
                @if ($subscription->payment_gateway)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Payment Gateway</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-semibold">
                            {{ ucfirst($subscription->payment_gateway) }}</dd>
                    </div>
                @endif
                @if ($subscription->gateway_subscription_id)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Gateway Subscription ID</dt>
                        <dd
                            class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-mono text-xs bg-white px-2 py-1 rounded">
                            {{ $subscription->gateway_subscription_id }}</dd>
                    </div>
                @endif
                @if ($subscription->gateway_customer_id)
                    <div>
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Gateway Customer</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                            {{ class_basename($subscription->gateway_customer_type) }}
                            #{{ $subscription->gateway_customer_id }}
                            @if ($subscription->gatewayCustomer)
                                - {{ $subscription->gatewayCustomer->name ?? '' }}
                            @endif
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Cancel at Period End</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                        <span class="inline-flex items-center">
                            @if ($subscription->cancel_at_period_end)
                                <span class="text-orange-600 font-semibold">Yes</span>
                            @else
                                <span class="text-green-600 font-semibold">No</span>
                            @endif
                        </span>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Reminders History -->
        <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                <span class="material-icons-outlined mr-2">notifications_active</span>
                Reminders & Notifications ({{ $subscription->reminders->count() }})
            </h3>
            
            @if ($subscription->reminders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-300">
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Channel</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Reminder #</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Scheduled</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Sent</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($subscription->reminders->sortByDesc('created_at') as $reminder)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $reminder->getReadableType() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        <span class="capitalize">{{ $reminder->channel }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                        {{ $reminder->reminder_number }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                        @if ($reminder->scheduled_at)
                                            {{ $reminder->scheduled_at->format('M d, Y H:i') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                        @if ($reminder->sent_at)
                                            {{ $reminder->sent_at->format('M d, Y H:i') }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'sent' => 'bg-green-100 text-green-800',
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'failed' => 'bg-red-100 text-red-800',
                                                'skipped' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $statusColor = $statusColors[$reminder->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ ucfirst($reminder->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs max-w-xs truncate" title="{{ $reminder->message }}">
                                        {{ $reminder->message ? Str::limit($reminder->message, 50) : '-' }}
                                    </td>
                                </tr>
                                @if ($reminder->error_message)
                                    <tr class="bg-red-50 dark:bg-red-900/20">
                                        <td colspan="7" class="px-4 py-2 text-xs text-red-700 dark:text-red-300">
                                            <strong>Error:</strong> {{ $reminder->error_message }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6">
                    <span class="material-icons-outlined text-gray-400 text-4xl mb-2 block">mail_outline</span>
                    <p class="text-gray-600 dark:text-gray-400">No reminders sent yet</p>
                </div>
            @endif
        </div>

        <!-- Metadata -->
        @if (!empty($subscription->metadata))
            <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center">
                    <span class="material-icons-outlined mr-2">info</span>
                    Additional Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($subscription->metadata as $key => $value)
                        <div>
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="text-sm text-gray-900 dark:text-gray-100 mt-1">
                                @if (is_array($value))
                                    <pre class="text-xs bg-white p-2 rounded">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if ($subscription->notes)
            <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Notes</h3>
                <p class="text-sm text-gray-900 dark:text-gray-100">{{ $subscription->notes }}</p>
            </div>
        @endif
    </div>

    <!-- Features Section -->
    <div class="bg-white border border-gray-200 rounded-lg mt-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="material-icons-outlined text-blue-500 mr-2">assignment</span>
                Features & Resources
                @if($subscription->activeFeatures()->count() > 0)
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $subscription->activeFeatures()->count() }} active
                    </span>
                @endif
            </h3>
            @if($subscription->subscriptionFeatures()->count() > 0)
                <a href="{{ route('subscriptions.features.index', $subscription->id) }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <span class="material-icons-outlined text-sm mr-1">manage_accounts</span>
                    Manage Features
                </a>
            @endif
        </div>

        @php
            $features = $subscription->subscriptionFeatures()
                ->get()
                ->groupBy(function($feature) {
                    return $feature->group ?? 'Other';
                });
        @endphp

        @if($subscription->subscriptionFeatures()->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($features as $group => $groupFeatures)
                <div class="px-6 py-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ $group ?: 'Other' }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($groupFeatures as $feature)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border {{ $feature->pivot->is_active ? 'border-green-200' : 'border-gray-200' }}">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $feature->display_name }}</p>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    {{ $feature->pivot->value }}
                                    @if($feature->unit)
                                        <span class="text-xs text-gray-500">{{ $feature->unit }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2 ml-2">
                                @if($feature->type === 'limit')
                                    <a href="{{ route('subscriptions.features.show', [$subscription->id, $feature->id]) }}" 
                                       class="text-blue-600 hover:text-blue-900 text-xs font-medium"
                                       title="View usage">
                                        <span class="material-icons-outlined text-sm">analytics</span>
                                    </a>
                                @endif
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $feature->pivot->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $feature->pivot->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <span class="material-icons-outlined text-gray-300 text-6xl mb-4">dashboard</span>
                <p class="text-gray-500 text-lg font-medium">No features assigned</p>
                <p class="text-gray-400 text-sm mt-2">This subscription doesn't have any features assigned yet. They will be automatically assigned when the plan is upgraded.</p>
            </div>
        @endif
    </div>
@endsection
