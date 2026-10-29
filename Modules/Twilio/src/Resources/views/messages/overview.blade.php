@extends('backend::layouts.app')

@section('title', 'Message Overview | '.$message->id)

@section('content')
@include('admin::components.common.back-button', ['route' => route('admin.twilio.show', $message->phoneNumber->id), 'name' => 'Message #' . $message->id])

<div class="space-y-6">
    <!-- Message Status Card -->
    <div class="bg-white rounded-lg shadow-sm border-l-4 @if($message->status === 'failed') border-red-500 @elseif($message->status === 'sent') border-green-500 @elseif($message->status === 'queued') border-yellow-500 @else border-blue-500 @endif">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        @if($message->status === 'failed')
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        @elseif($message->status === 'sent')
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @elseif($message->status === 'queued')
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Message #{{ $message->id }}</h1>
                        <p class="text-sm text-gray-600">Status: <span class="font-semibold capitalize {{ $message->status === 'failed' ? 'text-red-600' : ($message->status === 'sent' ? 'text-green-600' : 'text-yellow-600') }}">{{ $message->status }}</span></p>
                    </div>
                </div>
                <div class="text-right">
                    @if($message->retry_count > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            {{ $message->retry_count }} {{ Str::plural('retry', $message->retry_count) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Message Details -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                Message Details
            </h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Recipient</dt>
                    <dd class="text-sm font-semibold text-gray-900">{{ $message->to }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Message Body</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md mt-1">
                        <pre class="whitespace-pre-wrap font-sans">{{ $message->body }}</pre>
                    </dd>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Delivery SID</dt>
                        <dd class="text-sm font-mono text-gray-900">{{ $message->delivery_sid ?? 'Not available' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Sent At</dt>
                        <dd class="text-sm text-gray-900">{{ optional($message->sent_at)->format('M j, Y g:i A') ?? 'Not sent' }}</dd>
                    </div>
                </div>
            </dl>
        </div>

        <!-- Delivery Information -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Delivery Information
            </h2>
            <dl class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-500">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $message->status === 'failed' ? 'bg-red-100 text-red-800' : ($message->status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($message->status) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-500">Attempts:</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $message->retry_count + 1 }}</span>
                </div>
                @if($message->error)
                    <div>
                        <dt class="text-sm font-medium text-red-600 mb-1">Error Details</dt>
                        <dd class="text-sm text-red-700 bg-red-50 p-3 rounded-md border-l-4 border-red-400">
                            <div class="font-mono text-xs">{{ $message->error }}</div>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    <!-- Response Section -->
    @if($message->response)
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            Response Information
            @if($message->is_auto_response)
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Generated
                </span>
            @endif
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Response Content</h3>
                <div class="bg-purple-50 p-4 rounded-md border-l-4 {{ $message->is_auto_response ? 'border-l-blue-400' : 'border-l-purple-400' }}">
                    @if($message->is_auto_response)
                        <div class="flex items-center mb-2">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            <span class="text-xs font-medium text-blue-800">AI-Generated Response</span>
                        </div>
                    @endif
                    <pre class="text-sm {{ $message->is_auto_response ? 'text-blue-900' : 'text-purple-900' }} whitespace-pre-wrap font-sans">{{ $message->response }}</pre>
                </div>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Response Timeline</h3>
                <div class="space-y-2">
                    @if($message->response_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Received:</span>
                            <span class="font-semibold">{{ $message->response_at->format('M j, Y g:i A') }}</span>
                        </div>
                        @if($message->sent_at && $message->response_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Response Time:</span>
                                <span class="font-semibold">{{ $message->sent_at->diffForHumans($message->response_at, true) }}</span>
                            </div>
                        @endif
                    @endif
                    @if($message->is_auto_response && $message->originalMessage)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Triggered by:</span>
                            <span class="font-semibold">Message #{{ $message->originalMessage->id }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Timeline Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Message Timeline
        </h2>
        <div class="space-y-4">
            <!-- Created -->
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">Message Created</p>
                    <p class="text-sm text-gray-500">{{ $message->created_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>

            @if($message->sent_at)
            <!-- Sent -->
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">Message Sent</p>
                    <p class="text-sm text-gray-500">{{ $message->sent_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
            @endif

            @if($message->response_at)
            <!-- Response -->
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">Response Received</p>
                    <p class="text-sm text-gray-500">{{ $message->response_at->format('M j, Y g:i A') }}</p>
                </div>
            </div>
            @endif

            @if($message->status === 'failed' && $message->error)
            <!-- Failed -->
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">Delivery Failed</p>
                    <p class="text-sm text-gray-500">{{ $message->updated_at->format('M j, Y g:i A') }}</p>
                    <p class="text-sm text-red-600 mt-1">{{ Str::limit($message->error, 100) }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
