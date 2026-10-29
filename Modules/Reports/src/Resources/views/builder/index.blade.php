@extends('backend::layouts.app')

@section('title', 'Report Builder')

@section('breadcrumbs')
@php
  $breadcrumbs = [
    ['label' => 'Dashboard', 'url' => route('dashboard.index')],
    ['label' => 'System Settings', 'url' => route('settings.index')],
    ['label' => 'Reports', 'url' => route('reports.index')],
    ['label' => 'Report Builder', 'url' => null],
  ];
@endphp

<x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="'Report Builder'" />
@endsection

@section('content')
<div class="space-y-6" id="reportBuilder">
    <!-- Header -->
    <div class="bg-topbar rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold">Visual Report Builder</h1>
        <p class="mt-2 text-purple-100">Create powerful reports without writing any code</p>
    </div>

    <!-- Builder Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Configuration Panel -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Step 1: Data Source -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 text-sm font-bold mr-2">1</span>
                        Select Data Source
                    </h2>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Source Type</label>
                        <select v-model="report.data_source_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="table">Database Table</option>
                            <option value="model">Eloquent Model</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Source</label>
                        <select v-model="report.data_source" @change="loadColumns" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">-- Select Data Source --</option>
                            <optgroup label="Tables" v-if="report.data_source_type === 'table'">
                                <option v-for="table in dataSources.tables" :key="table" :value="table">
                                    @{{ table }}
                                </option>
                            </optgroup>
                            <optgroup label="Models" v-if="report.data_source_type === 'model'">
                                <option v-for="(modelClass, modelName) in dataSources.models" :key="modelName" :value="modelName">
                                    @{{ modelName }}
                                </option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 2: Select Columns -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" v-if="availableColumns.length > 0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 text-sm font-bold mr-2">2</span>
                        Select Columns
                    </h2>
                    <button @click="selectAllColumns" class="text-sm text-purple-600 hover:text-purple-800">Select All</button>
                </div>

                <div class="grid grid-cols-2 gap-3 max-h-64 overflow-y-auto">
                    <div v-for="column in availableColumns" :key="column.name" class="flex items-center">
                        <input type="checkbox" :id="'col-' + column.name" :value="column" v-model="selectedColumns" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <label :for="'col-' + column.name" class="ml-2 text-sm text-gray-700">
                            @{{ column.label }}
                            <span class="text-xs text-gray-500">(@{{ column.type }})</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 3: Add Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" v-if="selectedColumns.length > 0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 text-sm font-bold mr-2">3</span>
                        Add Filters (Optional)
                    </h2>
                    <button @click="addFilter" class="text-sm text-purple-600 hover:text-purple-800">+ Add Filter</button>
                </div>

                <div class="space-y-3">
                    <div v-for="(filter, index) in filters" :key="index" class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                        <select v-model="filter.column" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Column</option>
                            <option v-for="col in selectedColumns" :key="col.name" :value="col.name">@{{ col.label }}</option>
                        </select>
                        
                        <select v-model="filter.operator" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Operator</option>
                            <option v-for="op in operators" :key="op.value" :value="op.value">@{{ op.label }}</option>
                        </select>
                        
                        <input type="text" v-model="filter.value" placeholder="Value" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        
                        <button @click="removeFilter(index)" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div v-if="filters.length > 1" class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">Filter Logic:</span>
                        <label class="inline-flex items-center">
                            <input type="radio" v-model="report.filter_logic" value="AND" class="text-purple-600">
                            <span class="ml-1 text-sm">AND</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" v-model="report.filter_logic" value="OR" class="text-purple-600">
                            <span class="ml-1 text-sm">OR</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 4: Chart Type -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" v-if="selectedColumns.length > 0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 text-sm font-bold mr-2">4</span>
                        Choose Visualization
                    </h2>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <div v-for="chartType in chartTypes" :key="chartType.value" 
                         @click="report.chart_type = chartType.value"
                         :class="{'border-purple-500 bg-purple-50': report.chart_type === chartType.value, 'border-gray-300': report.chart_type !== chartType.value}"
                         class="flex flex-col items-center justify-center p-4 border-2 rounded-lg cursor-pointer hover:border-purple-400 transition-colors">
                        <svg class="w-8 h-8 mb-2" :class="report.chart_type === chartType.value ? 'text-purple-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-xs font-medium" :class="report.chart_type === chartType.value ? 'text-purple-600' : 'text-gray-600'">
                            @{{ chartType.label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="space-y-6">
            <!-- Report Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Configuration</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Name *</label>
                        <input type="text" v-model="report.name" placeholder="Enter report name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea v-model="report.description" placeholder="Optional description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" v-model="report.is_public" id="is_public" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                        <label for="is_public" class="ml-2 text-sm text-gray-700">Make this report public</label>
                    </div>

                    <div class="pt-4 border-t space-y-2">
                        <button @click="previewReport" :disabled="!canPreview" class="w-full px-4 py-2 bg-primary rounded-lg disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            Preview Report
                        </button>
                        <button @click="saveReport" :disabled="!canSave" class="w-full px-4 py-2 bg-dark text-white rounded-lg disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                            Save Report
                        </button>
                        <a href="{{ route('reports.index') }}" class="block w-full px-4 py-2 bg-gray-200 text-gray-700 text-center rounded-lg hover:bg-gray-300 transition-colors">
                            Cancel
                        </a>
                    </div>

                    <div v-if="previewLoading" class="text-center py-4">
                        <svg class="animate-spin h-8 w-8 mx-auto text-purple-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-gray-600 mt-2">Loading preview...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Results -->
    <div v-if="previewData" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview Results</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th v-for="(value, key) in previewData[0]" :key="key" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            @{{ key }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(row, index) in previewData.slice(0, 10)" :key="index">
                        <td v-for="(value, key) in row" :key="key" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @{{ value }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="text-sm text-gray-600 mt-4">Showing @{{ Math.min(10, previewData.length) }} of @{{ previewData.length }} rows</p>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            dataSources: @json($dataSources),
            report: {
                name: '',
                description: '',
                data_source: '',
                data_source_type: 'table',
                filter_logic: 'AND',
                chart_type: 'table',
                is_public: false,
            },
            availableColumns: [],
            selectedColumns: [],
            filters: [],
            operators: [],
            chartTypes: [],
            previewData: null,
            previewLoading: false,
        };
    },
    computed: {
        canPreview() {
            return this.report.data_source && this.selectedColumns.length > 0;
        },
        canSave() {
            return this.report.name && this.canPreview;
        }
    },
    mounted() {
        this.loadOperators();
        this.loadChartTypes();
    },
    methods: {
        async loadColumns() {
            if (!this.report.data_source) return;

            try {
                const response = await fetch('{{ route("reports.builder.get-columns") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        data_source: this.report.data_source,
                        type: this.report.data_source_type
                    })
                });

                const result = await response.json();
                if (result.success) {
                    this.availableColumns = result.columns;
                    this.selectedColumns = [];
                }
            } catch (error) {
                console.error('Error loading columns:', error);
            }
        },
        async loadOperators() {
            try {
                const response = await fetch('{{ route("reports.builder.operators") }}');
                const result = await response.json();
                if (result.success) {
                    this.operators = result.operators;
                }
            } catch (error) {
                console.error('Error loading operators:', error);
            }
        },
        async loadChartTypes() {
            try {
                const response = await fetch('{{ route("reports.builder.chart-types") }}');
                const result = await response.json();
                if (result.success) {
                    this.chartTypes = result.chart_types;
                }
            } catch (error) {
                console.error('Error loading chart types:', error);
            }
        },
        selectAllColumns() {
            this.selectedColumns = [...this.availableColumns];
        },
        addFilter() {
            this.filters.push({ column: '', operator: '', value: '' });
        },
        removeFilter(index) {
            this.filters.splice(index, 1);
        },
        async previewReport() {
            this.previewLoading = true;
            try {
                const response = await fetch('{{ route("reports.builder.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        data_source: this.report.data_source,
                        data_source_type: this.report.data_source_type,
                        guards: ["{{ $guard }}"],
                        columns: this.selectedColumns.map(col => ({ name: col.name })),
                        filters: this.filters.filter(f => f.column && f.operator),
                        filter_logic: this.report.filter_logic,
                        limit: 100
                    })
                });

                const result = await response.json();
                if (result.success) {
                    this.previewData = result.data;
                } else {
                    showToast('Preview failed: ' + result.error);
                    showToast('Preview fail', 'error', 'Error');
                }
            } catch (error) {
                console.error('Error previewing report:', error);
                showToast('Failed to preview report', 'error', 'Error');
            } finally {
                this.previewLoading = false;
            }
        },
        async saveReport() {
            if (!this.canSave) return;

            try {
                const response = await fetch('{{ route("reports.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ...this.report,
                        guards: ["{{ $guard }}"],
                        columns: this.selectedColumns.map(col => ({ name: col.name, alias: col.label })),
                        filters: this.filters.filter(f => f.column && f.operator)
                    })
                });

                if (response.ok) {
                    window.location.href = '{{ route("reports.index") }}';
                } else {
                    showToast(response.errors, 'error', 'Error');
                }
            } catch (error) {
                console.error('Error saving report:', error);
                showToast('Failed to save report', 'error', 'Error');
            }
        }
    }
}).mount('#reportBuilder');
</script>
@endsection
@endsection
