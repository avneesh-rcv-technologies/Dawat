@extends('backend::layouts.app')

@section('title', 'Orders')

@section('content')
@push('styles')
<link href="{{ asset('vendor/filemanager/css/filemanager.css') }}" rel="stylesheet">
<link href="{{ asset('vendor/dropzone/dropzone.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('vendor/dropzone/dropzone.js') }}"></script>
<script src="{{ asset('vendor/filemanager/js/filemanager.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.fileManager.init();
    });
</script>
@endpush

@section('content')
<div class="flex">

    <div class="max-w-4xl ml-12 mr-12">
        <div class="mt-4 flex items-center justify-between">
            <div class="flex">
                @include('admin::components.common.back-button', ['route' => route('admin.orders.show', $order->id), 'name' => ''])
                <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
            </div>

        </div>
        <p class="text-gray-600 mt-2 mb-4">Attachments</p>

        <div class="grid grid-cols-3 gap-4">

            <!-- Profile Photo Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="border-b border-gray-100 pb-4 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                        Profile Photograph
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Upload a high-resolution photograph (JPG, PNG, max 5MB)</p>
                </div>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors hover:border-blue-400">
                    @include('filemanager::components.file-uploader', [
                    'object_type' => 'order_photo',
                    'object_id' => $order->id,
                    'name' => "order_photo[{$order->id}][photo]",
                    'id' => 'photo-uploader-' . $order->id,
                    'isMultiple' => false,
                    'store' => true,
                    'type' => 'A',
                    'is_delete' => false,
                    'is_update' => false,
                    ])
                </div>
            </div>

            <!-- Bio Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="border-b border-gray-100 pb-4 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385V4.804zM11 4.804A7.968 7.968 0 0114.5 4c1.255 0 2.443.29 3.5.804v10A7.968 7.968 0 0014.5 14c-1.669 0-3.218.51-4.5 1.385V4.804z" />
                        </svg>
                        Bio
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Upload your bio document (PDF, DOCX, TXT, max 2MB)</p>
                </div>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors hover:border-blue-400">
                    @include('filemanager::components.file-uploader', [
                    'object_type' => 'order_bio',
                    'object_id' => $order->id,
                    'name' => "order_bio[{$order->id}][bio]",
                    'id' => 'bio-uploader-' . $order->id,
                    'isMultiple' => false,
                    'store' => true,
                    'type' => 'A',
                    'is_delete' => false,
                    'is_update' => false,
                    ])
                </div>
            </div>

            <!-- Dynamic Group Benefit Maps Sections -->
            @foreach($group_benefit_maps as $groupId => $benefits)
            @foreach($benefits as $benefit)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <div class="border-b border-gray-100 pb-4 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385V4.804zM11 4.804A7.968 7.968 0 0114.5 4c1.255 0 2.443.29 3.5.804v10A7.968 7.968 0 0014.5 14c-1.669 0-3.218.51-4.5 1.385V4.804z" />
                        </svg>
                        {{ is_array($benefit['values'] ?? null) ? implode(', ', $benefit['values']) : ($benefit['values'] ?? 'Benefit Document') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Please upload the required document (PDF, DOCX, JPG, PNG, max 5MB)</p>
                </div>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-colors hover:border-blue-400">
                @include('filemanager::components.file-uploader', [
                            'object_type' => 'benefit_upload',
                            'object_id' => $order->id . $benefit['benefit_id'],
                            'name' => "benefit_uploads[{$order->id}][{$benefit['benefit_id']}][file]",
                            'id' => 'benefit-uploader-' . $order->id . '-' . $benefit['benefit_id'],
                            'isMultiple' => false,
                            'store' => true,
                            'type' => 'A',
                            'is_delete' => false,
                            'is_update' => false,
                            'delete_route' => route('customer.attachments.destroy', ['id' => '__id__'])
                        ])
                   
                </div>
            </div>
            @endforeach
            @endforeach
        </div>

    </div>
    <div class="w-1/3 h-[calc(100vh-100px)] mt-28">
        <form action="{{ route('admin.orders.attachment.update', $order->id) }}" method="POST" class="mb-4 bg-white p-4 rounded-lg shadow">
            @csrf
            @method('PUT')
            <h3 class="text-md font-medium text-black-100 mb-2">Attachment Status</h3>

            <div class="mb-3">
                <select name="status" id="attachment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="1" @if(isset($attachment) && $attachment->status) selected @endif>Accept</option>
                    <option value="0" @if(isset($attachment) && !$attachment->status) selected @endif>Reject</option>
                </select>
            </div>

            <div id="rejection_reason_div" class="mb-3" style="display: {{ isset($attachment) && !$attachment->status ? 'block' : 'none' }};">
                <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                <textarea name="rejection_reason" id="rejection_reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('rejection_reason', $attachment->rejection_reason ?? '') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Update Status
                </button>
            </div>

            <script>
                document.getElementById('attachment_status').addEventListener('change', function() {
                    const reasonDiv = document.getElementById('rejection_reason_div');
                    reasonDiv.style.display = this.value === '0' ? 'block' : 'none';

                    // Clear rejection reason when switching to Accept
                    if (this.value === '1') {
                        document.getElementById('rejection_reason').value = '';
                    }
                });
            </script>
        </form>
    </div>

</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('attachments-form');
        const submitBtn = form.querySelector('button[type="submit"]');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading state
            submitBtn.disabled = true;
            if (submitText) submitText.textContent = 'Uploading...';
            if (submitSpinner) submitSpinner.classList.remove('hidden');

            // Submit form
            const formData = new FormData(form);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_url || "{{ route('customer.orders.show', $order->id) }}";
                    } else {
                        throw new Error(data.message || 'Failed to upload files');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while uploading the files. Please try again.');

                    // Reset button state
                    submitBtn.disabled = false;
                    if (submitText) submitText.textContent = 'Save Attachments';
                    if (submitSpinner) submitSpinner.classList.add('hidden');
                });
        });
    });
</script>
@endpush

@endsection