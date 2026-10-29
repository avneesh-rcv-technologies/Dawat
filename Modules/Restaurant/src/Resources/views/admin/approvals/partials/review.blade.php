<div id="onboarding-review-panel" class="space-y-5 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold">Review</h3>
            <p class="review-help">Review the saved restaurant details and onboarding decisions before printing.</p>
        </div>
        @if(bouncer('admin')->hasPermission('admin.restaurant.approvals.review.pdf'))
            <a href="{{ route('admin.restaurant.approvals.review.pdf', $restaurant->id) }}" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">Download PDF</a>
        @endif
    </div>
    @include('restaurant::admin.approvals.partials.review-content')
</div>
@include('restaurant::admin.approvals.partials.review-styles')
