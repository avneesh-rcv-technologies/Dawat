@extends('backend::layouts.app')

@section('title', 'KYC')

@section('breadcrumbs')
@php
    $breadcrumbs = [
        ['label' => 'Dashboard', 'url' => route('dashboard.index')],
        ['label' => 'KYC'],
    ];
@endphp
<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'KYC'" />
@endsection

@section('content')
<style>
    .kyc-empty-state { max-width: 640px; margin: 0 auto; padding: 56px 24px; border: 1px solid #EEE9E4; border-radius: 16px; background: #fff; text-align: center; box-shadow: 0 1px 2px rgba(17, 17, 17, .04), 0 8px 24px -12px rgba(17, 17, 17, .08); }
    .kyc-empty-state-icon { display: flex; align-items: center; justify-content: center; width: 64px; height: 64px; margin: 0 auto 20px; border-radius: 18px; background: #FFF4EE; color: #FF6210; }
    .kyc-empty-state-eyebrow { color: #B8430A; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    .kyc-empty-state h2 { margin-top: 8px; color: #111111; font-size: 24px; line-height: 1.3; font-weight: 800; }
    .kyc-empty-state p.kyc-empty-state-copy { max-width: 440px; margin: 8px auto 0; color: #64748B; font-size: 14px; line-height: 1.7; }
</style>
<section class="kyc-empty-state" aria-labelledby="kyc-empty-title">
    <span class="kyc-empty-state-icon" aria-hidden="true">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M12 18v-6m-3 3 3-3 3 3"/></svg>
    </span>
    <p class="kyc-empty-state-eyebrow">Document verification</p>
    <h2 id="kyc-empty-title">No KYC documents submitted yet</h2>
    <p class="kyc-empty-state-copy">Your KYC details will appear here once you submit your documents. You'll be able to track each document's review status and messages from this page.</p>
</section>
@endsection
