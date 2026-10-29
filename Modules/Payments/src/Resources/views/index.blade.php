@extends('backend::layouts.app')

@section('title', 'Payments')

@section('breadcrumbs')
    @php
        $title = 'Payments';
        $breadcrumbs = [
            ['label' => 'Settings', 'url' => route('settings.index')],
            ['label' => $title],
        ];
    @endphp

    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

<x-data-view :data="$lists" title="" url="" />
   
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const configContainer = document.querySelector('.tab-config');

    function loadConfigTemplate(processorCode) {
        if (!processorCode) {
            configContainer.innerHTML = ''; // Clear config if no selection
            return;
        }

        if (typeof ceAjax !== 'function') {
            console.error('ceAjax is not defined');
            return;
        }

        ceAjax('GET', `{{ route('admin.payment_methods.config', '___CODE___') }}`.replace('___CODE___', processorCode), {
            loader: true,
            caching: false,
            data: {
                processorCode:processorCode
            },
            result_ids: 'payment_configuration',
            callback: function (response) {
                if(response.errors) {
                    // $.each(response.errors, function (i, v) {
                    //    showToast(v, 'error', 'Error');
                    // });
                }
            },
            errorCallback: function () {
                configContainer.innerHTML = `<div class="text-red-500">Error loading configuration for <strong>${processorCode}</strong>.</div>`;
            }
        });
    }

    // Use event delegation for processor select changes
    document.body.addEventListener('change', function (e) {
        if (e.target.matches('select[name="processor"]')) {
            loadConfigTemplate(e.target.value);
        }
    });

    // Initial load on page ready if a processor is already selected
    const initialSelect = document.querySelector('select[name="processor"]');
    
    if (initialSelect && initialSelect.value) {
        loadConfigTemplate(initialSelect.value);
    }
});
</script>
@endsection