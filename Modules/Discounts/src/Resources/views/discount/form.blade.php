@extends('backend::layouts.app')
@section('title', isset($discount) ? 'Edit Discount' : 'Create Discount')

@section('breadcrumbs')
    @php
        $title = isset($discount) ? 'Edit Discount' : 'Create Discount';
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard.index')],
            ['label' => 'Discounts', 'url' => route('discount.index')],
            ['label' => $title],
        ];
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
    <form class="form-ajax" method="POST"
        action="@isset($discount) {{ route('discount.update', $discount->id) }} @else {{ route('discount.store') }} @endisset"
        enctype="multipart/form-data" id="discountForm">
        @csrf
        @isset($discount) @method('PUT') @endisset

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button type="button" data-tab="general"
                    class="tab-button active border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    General
                </button>
                <button type="button" data-tab="rules"
                    class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Rules
                </button>
                <button type="button" data-tab="coupons"
                    class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Coupons
                </button>
                @isset($discount)
                <button type="button" data-tab="history"
                    class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Usage History
                </button>
                @endisset
            </nav>
        </div>

        <!-- Tab Contents -->
        <div class="tab-content" data-tab-content="general">
            @include('discounts::discount.components.general')
        </div>

        <div class="tab-content hidden" data-tab-content="rules">
            @include('discounts::discount.components.rule')
        </div>

        <div class="tab-content hidden" data-tab-content="coupons">
            @include('discounts::discount.components.coupons')
        </div>

        @isset($discount)
        <div class="tab-content hidden" data-tab-content="history">
            @include('discounts::discount.components.history')
        </div>
        @endisset

        <!-- Submit -->
        <div class="flex space-x-3 mt-6">
            <x-button
                type="submit"
                class="btn-primary"
                label="Save Changes"
                icon=""
                name="button"
            />
        </div>

    </form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Add active class to clicked button
            this.classList.add('active', 'border-indigo-500', 'text-indigo-600');
            this.classList.remove('border-transparent', 'text-gray-500');

            // Show corresponding content
            const targetContent = document.querySelector(`[data-tab-content="${targetTab}"]`);
            if (targetContent) {
                targetContent.classList.remove('hidden');
            }
        });
    });

    // Discount type label change
    const discountType = document.getElementById('discount-type');
    const valueTypeLabel = document.getElementById('value-type-label');

    if (discountType && valueTypeLabel) {
        discountType.addEventListener('change', function() {
            if (this.value === 'P') {
                valueTypeLabel.textContent = '(%)';
            } else if (this.value === 'F') {
                valueTypeLabel.textContent = '($)';
            } else {
                valueTypeLabel.textContent = '';
            }
        });

        // Trigger on load
        discountType.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection