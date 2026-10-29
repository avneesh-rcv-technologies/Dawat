<div id="menu-panel" class="space-y-4 p-6">
    <h3 class="text-lg font-semibold text-gray-900">Menu Details</h3>
    @if(in_array(\Modules\Restaurant\Enums\OnboardingStep::MENU, \Modules\Restaurant\Enums\OnboardingStep::canSkip(), true))
        <p class="review-help">This step is optional and can be skipped during onboarding.</p>
    @endif
    @php $menuRecord = $stepRecords->get('menu'); @endphp
    @if($menuRecord?->rejection_reason)<p class="rounded-lg bg-amber-50 p-4 text-sm">{{ $menuRecord->rejection_reason }}</p>@endif
    @foreach(\Modules\FoodManagements\Models\FoodItem::where('restaurant_id', $restaurant->id)->orderBy('id')->get() as $item)
        <div class="rounded-lg border border-gray-200 p-4"><strong>{{ $item->name }}</strong><p>{{ $item->description }}</p><p>{{ $item->food_type === 'veg' ? 'Vegetarian' : 'Non-vegetarian' }} · {{ $item->is_available ? 'Available' : 'Unavailable' }}</p><p>Price: {{ $item->sale_price }} · List price: {{ $item->list_price ?? 'Not set' }}</p></div>
    @endforeach
    @if(in_array($menuRecord?->status, ['submitted', 'pending', 'action_required'], true) && bouncer('admin')->hasPermission('admin.restaurant.approvals.start-review'))
        <form method="POST" action="{{ route('admin.restaurant.approvals.start-review', $restaurant->id) }}">@csrf<input type="hidden" name="step_key" value="menu"><button type="submit" class="rounded-lg border px-4 py-2">Start review</button></form>
    @elseif($menuRecord?->status === 'under_review' && bouncer('admin')->hasPermission('admin.restaurant.approvals.menu.review'))
        <form method="POST" action="{{ route('admin.restaurant.approvals.menu.review', $restaurant->id) }}" class="space-y-3">@csrf
            <label class="block">Reason (required when rejecting)<textarea name="reason" maxlength="500" class="block w-full rounded-lg border p-3"></textarea></label>
            <button name="action" value="approve" class="rounded-lg border px-4 py-2">Approve menu</button>
            <button name="action" value="reject" class="rounded-lg border px-4 py-2">Reject menu</button>
        </form>
    @endif
</div>
