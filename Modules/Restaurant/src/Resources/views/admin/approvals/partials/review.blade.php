<div id="onboarding-review-panel">
    <div class="ar-panel-head">
        <div>
            <p class="ar-eyebrow">Summary</p>
            <h3>Review</h3>
            <p class="review-help">Review the saved restaurant details and onboarding decisions before printing.</p>
        </div>
        @if(bouncer('admin')->hasPermission('admin.restaurant.approvals.review.pdf'))
            <a href="{{ route('admin.restaurant.approvals.review.pdf', $restaurant->id) }}" class="ar-btn ar-btn--primary">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Download PDF
            </a>
        @endif
    </div>
    @include('restaurant::admin.approvals.partials.review-content')
</div>
@include('restaurant::admin.approvals.partials.review-styles')
