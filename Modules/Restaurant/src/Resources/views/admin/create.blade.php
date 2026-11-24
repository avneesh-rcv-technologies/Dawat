@extends('backend::layouts.app')

@section('title', $restaurantId ? 'Edit Restaurant' : 'Create Restaurant')

@section('breadcrumbs')
@php
$title = $restaurantId ? 'Edit Restaurant' : 'Create Restaurant';
$breadcrumbs = [
['label' => 'Dashboard', 'url' => route('dashboard.index')],
['label' => 'Restaurant', 'url' => route('admin.restaurant.index')],
['label' => $title],
];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

@php
$restaurantId = $restaurantId ?? $restaurant?->id ?? request('id');
$canManageFeatures = $restaurant && $restaurant->canManageFeatures();
$isRestaurantOwner = app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user());

@endphp

@php
// Check URL query parameter first, then session, then default
$activeTab = request('tab') ?: session('tab', 'general');
if (!in_array($activeTab, ['general', 'settings', 'gallery'], true)
    || (!$canManageFeatures && $activeTab !== 'general')
    || ($isRestaurantOwner && in_array($activeTab, ['settings', 'gallery'], true))) {
    $activeTab = 'general';
}

@endphp

<div class="mx-auto">
    @if($restaurantId && !$canManageFeatures)
        <div role="status" class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Your Onboarding is not complete. You cannot manage restaurant features until both onboarding and application approval are completed.
        </div>
    @endif

    {{-- ===================== TABS HEADER ===================== --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="flex border-b border-gray-200">
            <button class="tab-btn {{ $activeTab === 'general' ? 'tab-active' : '' }}" data-tab="general">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                General Information
            </button>

            @if ($restaurantId)
            @unless($isRestaurantOwner)
            <button class="tab-btn {{ $activeTab === 'settings' ? 'tab-active' : '' }}" data-tab="settings" @disabled(!$canManageFeatures) aria-disabled="{{ $canManageFeatures ? 'false' : 'true' }}" title="{{ $canManageFeatures ? 'Settings' : 'Both onboarding and application approval are required' }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Settings
            </button>

            @endunless

            @unless($isRestaurantOwner)
            <button class="tab-btn {{ $activeTab === 'gallery' ? 'tab-active' : '' }}" data-tab="gallery" @disabled(!$canManageFeatures) aria-disabled="{{ $canManageFeatures ? 'false' : 'true' }}" title="{{ $canManageFeatures ? 'Gallery' : 'Both onboarding and application approval are required' }}">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                Gallery
            </button>
            @endunless
            @endif

        </div>
    </div>


    {{-- ===================== TAB CONTENT ===================== --}}

    {{-- TAB 1 : GENERAL --}}
    <div class="tab-content {{ $activeTab !== 'general' ? 'hidden' : '' }}" id="tab-general">
        <div class="space-y-6">

            @include('restaurant::admin.partials.general-information-form')

            @unless(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user()))
                @include('restaurant::admin.partials.working-hours-form')
            @endunless

            @unless(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner(auth('admin')->user()))
                @include('restaurant::admin.partials.holidays-form')
            @endunless

        </div>
    </div>
    {{-- TAB 2 : ADDRESS --}}
    @if ($restaurantId && $canManageFeatures)

    @unless($isRestaurantOwner)
    {{-- TAB 5 : SETTINGS --}}
    <div class="tab-content {{ $activeTab !== 'settings' ? 'hidden' : '' }}" id="tab-settings">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">

            <form method="POST" action="{{ route('admin.restaurant.update.settings', $restaurantId) }}">
                @csrf

                <div class="space-y-8">
                    @include('restaurant::admin.partials.restaurant-settings-fields')

                    @include('restaurant::admin.partials.delivery-policy')

                    {{-- Save Button --}}
                    <div class="flex justify-end mt-8">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @endunless

    @unless($isRestaurantOwner)
    {{-- TAB 6 : GALLERY --}}
    <div class="tab-content {{ $activeTab !== 'gallery' ? 'hidden' : '' }}" id="tab-gallery">
        @include('restaurant::admin.partials.gallery', ['restaurantId' => $restaurantId])
    </div>

    @endunless

    @endif

</div>

{{-- ===================== TAB SCRIPT ===================== --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
            const activeTab = "{{ $activeTab }}";

            /* ================= TAB INIT ================= */
            document.querySelectorAll('.tab-content')
                .forEach(c => c.classList.add('hidden'));

            document.getElementById('tab-' + activeTab)
                ?.classList.remove('hidden');

            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('tab-active'));

            document.querySelector(`.tab-btn[data-tab="${activeTab}"]`)
                ?.classList.add('tab-active');

            /* ================= TAB SWITCH ================= */
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (btn.disabled) return;
                    const tab = btn.dataset.tab;

                    document.querySelectorAll('.tab-content')
                        .forEach(c => c.classList.add('hidden'));

                    document.getElementById('tab-' + tab)
                        ?.classList.remove('hidden');

                    document.querySelectorAll('.tab-btn')
                        .forEach(b => b.classList.remove('tab-active'));

                    btn.classList.add('tab-active');

                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.replaceState(null, '', url.toString());
                });
            });

        });

        function generateSlug() {
            // If editing existing restaurant, do not overwrite slug
            @if ($restaurantId)
                return;
            @endif

            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            if (!nameInput || !slugInput) return;

            const slug = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            slugInput.value = slug;
        }
</script>

{{-- ===================== TAB STYLES ===================== --}}
@include('restaurant::admin.partials.restaurant-form-styles')

@include('restaurant::admin.partials.general-information-script')
@include('restaurant::admin.partials.restaurant-address-script')
@endsection
