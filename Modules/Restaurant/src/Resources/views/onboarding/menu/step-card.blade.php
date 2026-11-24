@php
    $menuItemCount = \Modules\FoodManagements\Models\FoodItem::where('restaurant_id', $restaurant->id)->count();
    $menuCategoryCount = \Modules\FoodManagements\Models\FoodCategory::where('restaurant_id', $restaurant->id)->count();
    $menuStarted = $menuItemCount > 0;
@endphp
<a href="{{ route('restaurant.onboarding.menu.index') }}" class="kyc-step-row-card">
    <div class="kyc-step-row-left">
        <div class="kyc-step-icon {{ $menuStarted ? 'is-complete' : '' }}">
            @if($menuStarted)
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            @else
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#71717A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
            @endif
        </div>
        <div class="kyc-step-texts">
            <div class="kyc-step-header-line">
                <h3 class="kyc-step-title">Menu details</h3>
                <span class="kyc-optional-tag">Optional</span>
            </div>
            <p class="kyc-step-sub">Add food items, categories and features</p>
            <div class="kyc-step-badge-line">
                <span class="kyc-step-chip {{ $menuStarted ? 'is-complete' : '' }}">
                    @if($menuStarted)
                        {{ $menuItemCount }} {{ \Illuminate\Support\Str::plural('item', $menuItemCount) }} · {{ $menuCategoryCount }} {{ \Illuminate\Support\Str::plural('category', $menuCategoryCount) }}
                    @else
                        Not started
                    @endif
                </span>
            </div>
        </div>
    </div>
    <div class="kyc-step-arrow" aria-hidden="true">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </div>
</a>
