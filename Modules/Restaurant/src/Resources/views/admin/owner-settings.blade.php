@extends('backend::layouts.app')

@section('title', 'Settings | ' . $title)

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'Settings', 'url' => route('settings.index')],
        ['label' => $title],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')
<style>
    .owner-restaurant-settings { padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
    .owner-restaurant-settings .form-label { display: block; margin-bottom: 8px; font-size: 14px; }
    .owner-restaurant-settings .input-field { width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; background-color: #fff; }
    .owner-restaurant-settings select.input-field:not([multiple]):not([size]),
    .owner-restaurant-settings select.input-field:not([multiple]):not([size]):focus {
        padding-right: 40px;
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 12px 8px;
    }
    .owner-restaurant-settings fieldset:disabled { opacity: .65; }
    .owner-restaurant-settings { padding: 28px 30px 0; border-radius: 18px; box-shadow: 0 2px 4px #10182804; }
    .owner-restaurant-settings > .restaurant-name { display: inline-flex; margin-bottom: 18px; padding: 6px 12px; border-radius: 999px; background: #fff3e9; color: #bd510d; font-size: 12px; }
    .owner-restaurant-settings fieldset { min-width: 0; }
    .owner-restaurant-settings .settings-save-bar { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-top: 0; padding: 22px 0; border-top: 1px solid #e5e7eb; }
    .owner-restaurant-settings .settings-save-bar p { margin: 0; color: #788397; font-size: 12px; }
    .owner-restaurant-settings .settings-save-bar button { padding: 12px 24px; border: 0; border-radius: 999px; background: #ff650f; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
    .owner-restaurant-settings .settings-save-bar button:hover { background: #e95500; }
    .owner-restaurant-settings .settings-save-bar button:focus-visible { outline: 3px solid #ffbd95; outline-offset: 3px; }
    @media (max-width: 540px) {
        .owner-restaurant-settings { padding: 20px 16px 0; }
        .owner-restaurant-settings .settings-save-bar { align-items: stretch; flex-direction: column; gap: 12px; }
    }
</style>
<div class="owner-restaurant-settings">
    <p class="restaurant-name mb-4 text-sm text-gray-600">{{ $restaurant->name }}</p>
    @unless($canUpdate)
        <p class="mb-4 text-sm text-gray-600">You have permission to view these settings. Update permission is required to save changes.</p>
    @endunless
    @if($errors->any())
        <div role="alert" class="mb-4 text-sm text-red-600">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    <form method="POST" action="{{ route('settings.restaurant.' . $section . '.update') }}">
        @csrf
        <fieldset @disabled(!$canUpdate)>
            <div class="space-y-8">
                @if($section === 'preferences')
                    @include('restaurant::admin.partials.restaurant-settings-fields')
                @else
                    @include('restaurant::admin.partials.delivery-policy')
                @endif
                <div class="settings-save-bar flex justify-end mt-8">
                    <p>Changes apply after you save your settings.</p>
                    <button type="submit" class="btn btn-primary">Save {{ $title }}</button>
                </div>
            </div>
        </fieldset>
    </form>
</div>
@endsection
