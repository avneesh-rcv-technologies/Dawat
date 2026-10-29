@extends('backend::layouts.app')

@section('title', 'Notification Details')

@section('content')
@php
    // Decode data if it's a string
    $notifData = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
    if (!is_array($notifData)) {
        $notifData = [];
    }
    
    // Extract title and body
    $title = $notifData['title'] ?? 'Notification';
    $body = $notifData['body'] ?? $notifData['message'] ?? $notifData['content'] ?? '';
    
    // If still empty, try to extract from notification type
    if ($title === 'Notification') {
        $type = class_basename($notification->type);
        $title = preg_replace('/Notification$/', '', $type);
        $title = preg_replace('/([a-z])([A-Z])/', '$1 $2', $title);
    }
    
    // If body is still empty, use the data as string
    if (empty($body) && is_string($notification->data)) {
        $body = strip_tags($notification->data);
    }
@endphp

<div class="max-w-xl mx-auto py-10">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold mb-2">{{ $title }}</h2>
        <div class="text-gray-600 mb-4">{{ $notification->created_at->format('M d, Y g:i A') }}</div>
        <div class="text-gray-800 mb-6 whitespace-pre-wrap">{{ $body }}</div>
        <a href="{{ route('system.notifications') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
