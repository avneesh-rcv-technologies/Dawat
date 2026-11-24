@extends('backend::layouts.app')

@section('title', $foodItemId ? 'Edit Food Item' : 'Create Food Item')

@section('breadcrumbs')
    @php
        $title = $foodItemId ? 'Edit Food Item' : 'Create Food Item';
        
        // Check if coming from restaurant context
        $restaurantId = session('food_item_restaurant_id');
        $restaurant = $restaurantId ? \Modules\Restaurant\Models\Restaurant::find($restaurantId) : null;
        
        if ($restaurant) {
            $breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard.index')],
                ['label' => 'Restaurants', 'url' => route('admin.restaurant.index')],
                ['label' => $restaurant->name, 'url' => route('admin.restaurant.edit', $restaurant->id)],
                ['label' => 'Food Management', 'url' => route('admin.restaurant.edit', $restaurant->id) . '?tab=food'],
                ['label' => $title],
            ];
        } else {
            $breadcrumbs = [
                ['label' => 'Dashboard', 'url' => route('dashboard.index')],
                ['label' => 'Food Items', 'url' => route('admin.foodmanagements.food-items.index')],
                ['label' => $title],
            ];
        }
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

    @php
        $activeTab = $errors->any() ? 'general' : session('tab', 'general');
        if (in_array($activeTab, ['tags', 'options', 'option_prices'], true)) {
            $activeTab = $foodItemId ? 'categories' : 'general';
        }
    @endphp

    <div class="mx-auto px-4 py-2">

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

                @if ($foodItemId)
                    <button class="tab-btn {{ $activeTab === 'categories' ? 'tab-active' : '' }}" data-tab="categories">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                        Categories
                    </button>

                    <button class="tab-btn {{ $activeTab === 'images' ? 'tab-active' : '' }}" data-tab="images">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        Images
                    </button>

                    <button class="tab-btn {{ $activeTab === 'features' ? 'tab-active' : '' }}" data-tab="features">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                        Features
                    </button>
                @endif
            </div>
        </div>

        {{-- ===================== TAB CONTENT ===================== --}}

        {{-- TAB 1 : GENERAL --}}
        <div class="tab-content {{ $activeTab !== 'general' ? 'hidden' : '' }}" id="tab-general">
            @include('foodmanagements::food_items.partials.general-form')
        </div>

        {{-- TAB 2 : SUB-CATEGORIES --}}
        @if ($foodItemId)
            <div class="tab-content {{ $activeTab !== 'categories' ? 'hidden' : '' }}" id="tab-categories">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Categories</h3>
                        <p class="text-sm text-gray-600">Select categories for this food item</p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.categories', $foodItemId) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($categories as $category)
                                <label
                                    class="flex items-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                        class="w-4 h-4 text-blue-600 rounded" @checked(isset($item) && $item->categories->contains($category->id))>

                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $category->parent ? $category->name : $category->name }}
                                        </span>

                                        @if ($category->parent)
                                            <p class="text-xs text-gray-500">
                                                Parent: {{ $category->parent->name }}
                                            </p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-8 space-x-3">
                            <button type="submit" class="btn btn-primary">
                                Save Categories
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TAB 5 : IMAGES --}}
            <div class="tab-content {{ $activeTab !== 'images' ? 'hidden' : '' }}" id="tab-images">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Images</h3>
                        <p class="text-sm text-gray-600">Upload images for this food item</p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.images', $foodItemId) }}"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            {{-- Upload New Images --}}
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-4">Upload New Images</h4>
                                @include('filemanager::components.file-uploader', [
                                    'object_type' => 'food_image',
                                    'object_id' => $foodItemId ?? 0,
                                    'name' => 'food_image[]',
                                    'is_multiple' => true,
                                    'accept' => '.jpg,.jpeg,.png,.webp',
                                    'max_size' => '2MB',
                                    'max_files' => 10,
                                    'append_images' => true,
                                    'span_text' => 'Add JPG, JPEG, PNG, or WebP images. Existing images are kept. Maximum 10 images in total.',
                                ])
                                @foreach (collect($errors->get('food_image'))->merge(collect($errors->get('food_image.*'))->flatten())->unique() as $message)
                                    <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end mt-8 space-x-3">
                            <button type="submit" class="btn btn-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                                Complete Food Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- TAB 6 : FEATURES --}}
            <div class="tab-content {{ $activeTab !== 'features' ? 'hidden' : '' }}" id="tab-features">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Food Features</h3>
                        <p class="text-sm text-gray-600">Assign features (e.g. Veg, Non-Veg) to this food item</p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.foodmanagements.food-items.update.features', $foodItemId) }}">
                        @csrf

                        @if ($featureGroups->isEmpty())
                            <div class="text-center py-12">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Feature Groups Found</h3>
                                <p class="text-gray-500">Please create feature groups and features first.</p>
                            </div>
                        @else
                            @php
                                // Pre-build a lookup: feature_id => saved values for this item
                                $savedValues = isset($item)
                                    ? $item->featureValues->groupBy('feature_id')
                                    : collect();
                            @endphp

                            <div class="space-y-6">
                                @foreach ($featureGroups as $group)
                                    @if ($group->children->isEmpty()) @continue @endif

                                    <div class="bg-gray-50 rounded-lg border p-4">
                                        <h4 class="text-base font-semibold text-gray-800 mb-4">
                                            {{ $group->description->name ?? 'Group #' . $group->feature_id }}
                                        </h4>

                                        <div class="space-y-4">
                                            @foreach ($group->children as $feature)
                                                @php
                                                    $fid        = $feature->feature_id;
                                                    $ftype      = $feature->feature_type; // C, S, M, N, O, T, D
                                                    
                                                    $fstyle     = $feature->feature_style;
                                                    $fname      = $feature->description->name ?? 'Feature #' . $fid;
                                                    $fSaved     = $savedValues->get($fid, collect());
                                                    $savedVariantIds = $fSaved->pluck('variant_id')->filter()->map(fn($v) => (string)$v)->toArray();
                                                    $savedValue = $fSaved->first()?->value ?? '';
                                                @endphp

                                                <div class="bg-white rounded-lg border p-4">
                                                    <label class="block text-sm font-semibold text-gray-800 mb-2">
                                                        {{ $fname }}
                                                        <span class="ml-1 text-xs font-normal text-gray-400">({{ strtoupper($ftype) }})</span>
                                                    </label>

                                                    {{-- C: Single Checkbox (yes/no) --}}
                                                    @if ($ftype === 'C')
                                                        <label class="inline-flex items-center gap-2 cursor-pointer">
                                                            <input type="hidden" name="features[{{ $fid }}]" value="">
                                                            <input type="checkbox"
                                                                name="features[{{ $fid }}]"
                                                                value="Y"
                                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                                                @if ($fSaved->isNotEmpty()) checked @endif>
                                                            <span class="text-sm text-gray-700">Yes</span>
                                                        </label>

                                                    {{-- S: Select — radio buttons (one variant) --}}
                                                    @elseif ($ftype === 'S')
                                                       
                                                        @if ($feature->variants->isEmpty())
                                                            <p class="text-xs text-gray-400">No variants defined.</p>
                                                        @else
                                                            @if ($fstyle === 'dropdown' || $fstyle === 'dropdown_labels')
                                                         
                                                                <select name="features[{{ $fid }}]"
                                                                    class="input-field max-w-xs text-sm">
                                                                   
                                                                    <option value="">— Select —</option>
                                                                   
                                                                    @foreach ($feature->variants as $variant)
                                                              
                                                                        @php $vname = $variant->description->description ?? 'Variant #' . $variant->variant_id; @endphp
                                                                        <option value="{{ $variant->variant_id }}"
                                                                            @selected(in_array((string)$variant->variant_id, $savedVariantIds))>
                                                                            {{ $vname }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                {{-- radio --}}
                                                                <div class="flex flex-wrap gap-3">
                                                                    @foreach ($feature->variants as $variant)
                                                                        @php $vname = $variant->description->description ?? 'Variant #' . $variant->variant_id; @endphp
                                                                        <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 bg-gray-50 rounded-lg border hover:bg-gray-100">
                                                                            <input type="radio"
                                                                                name="features[{{ $fid }}]"
                                                                                value="{{ $variant->variant_id }}"
                                                                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                                                                @checked(in_array((string)$variant->variant_id, $savedVariantIds))>
                                                                            <span class="text-sm text-gray-700">{{ $vname }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endif

                                                    {{-- M: Multiselect — multiple checkboxes (many variants) --}}
                                                    @elseif ($ftype === 'M')
                                                        @if ($feature->variants->isEmpty())
                                                            <p class="text-xs text-gray-400">No variants defined.</p>
                                                        @else
                                                            <div class="flex flex-wrap gap-3">
                                                                
                                                                @foreach ($feature->variants as $variant)
                                                                    @php $vname = $variant->description->description ?? 'Variant #' . $variant->variant_id; @endphp
                                                                    <label class="inline-flex items-center gap-2 cursor-pointer px-3 py-2 bg-gray-50 rounded-lg border hover:bg-gray-100">
                                                                        <input type="checkbox"
                                                                            name="features[{{ $fid }}][]"
                                                                            value="{{ $variant->variant_id }}"
                                                                            class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                                                            @checked(in_array((string)$variant->variant_id, $savedVariantIds))>
                                                                        <span class="text-sm text-gray-700">{{ $vname }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                    {{-- N / O: Number input --}}
                                                    @elseif (in_array($ftype, ['N', 'O']))
                                                        <input type="number" step="any"
                                                            name="features[{{ $fid }}]"
                                                            value="{{ $savedValue }}"
                                                            class="input-field max-w-xs text-sm"
                                                            placeholder="Enter value">

                                                    {{-- D: Date input --}}
                                                    @elseif ($ftype === 'D')
                                                  
                                                        <input type="date"
                                                            name="features[{{ $fid }}]"
                                                            value="{{ $savedValue }}"
                                                            class="input-field max-w-xs text-sm">

                                                    {{-- T / default: Text input --}}
                                                    @else
                                                        <input type="text"
                                                            name="features[{{ $fid }}]"
                                                            value="{{ $savedValue }}"
                                                            class="input-field max-w-xs text-sm"
                                                            placeholder="Enter value">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex justify-end mt-8">
                                <button type="submit" name="button" class="btn btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Features
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

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
                    const tab = btn.dataset.tab;

                    document.querySelectorAll('.tab-content')
                        .forEach(c => c.classList.add('hidden'));

                    document.getElementById('tab-' + tab)
                        ?.classList.remove('hidden');

                    document.querySelectorAll('.tab-btn')
                        .forEach(b => b.classList.remove('tab-active'));

                    btn.classList.add('tab-active');
                });
            });
        });

        /* ================= IMAGE PREVIEW ================= */
        function previewImages(input) {
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';

            if (input.files && input.files.length > 0) {
                preview.classList.remove('hidden');

                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const div = document.createElement('div');
                            div.className = 'relative';
                            div.innerHTML = `
                                <img src="${e.target.result}" alt="Preview ${index + 1}" 
                                     class="w-full h-24 object-cover rounded-lg border">
                                <span class="absolute top-1 right-1 bg-blue-500 text-white text-xs px-1 rounded">New</span>
                            `;
                            preview.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                preview.classList.add('hidden');
            }
        }

        /* ================= NOTIFICATION SYSTEM ================= */
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        /* ================= SWITCH TAB FUNCTION ================= */
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content')
                .forEach(c => c.classList.add('hidden'));

            document.getElementById('tab-' + tabName)
                ?.classList.remove('hidden');

            document.querySelectorAll('.tab-btn')
                .forEach(b => b.classList.remove('tab-active'));

            document.querySelector(`.tab-btn[data-tab="${tabName}"]`)
                ?.classList.add('tab-active');
        }

        /* ================= QUANTITY TYPE HANDLER ================= */
        function updateQuantityLabel() {
            const quantityType = document.getElementById('quantity_type').value;
            const quantityLabel = document.getElementById('quantity_label');
            const quantityHelpText = document.getElementById('quantity_help_text');
            const quantityPlaceholder = document.getElementById('quantity_placeholder');
            
            if (quantityType === 'grouping') {
                quantityLabel.textContent = 'Minimum Guests';
                quantityHelpText.textContent = 'Grouping: Works as minimum guest count';
                quantityPlaceholder.textContent = 'Enter minimum number of guests required';
            } else if (quantityType === 'piece') {
                quantityLabel.textContent = 'Pieces';
                quantityHelpText.textContent = 'Piece: Works as individual pieces';
                quantityPlaceholder.textContent = 'Enter number of pieces';
            } else {
                quantityLabel.textContent = 'Stock Quantity';
                quantityHelpText.textContent = 'Manual: Works as stock quantity';
                quantityPlaceholder.textContent = 'Enter available stock quantity';
            }
        }
    </script>

    {{-- ===================== TAB STYLES ===================== --}}
    <style>
        /* Form Elements */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .input-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            background-color: #ffffff;
        }

        .input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .input-field.input-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #FF6210;
            color: #ffffff;
        }

        /* Tab Styles */
        .tab-btn {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            background: none;
            border: none;
            border-radius: 0;
        }

        .tab-btn:hover {
            color: #374151;
            background-color: #f9fafb;
        }

        .tab-btn.tab-active {
            color: #FF6210;
            border-bottom-color: #FF6210;
            background-color: #eff6ff;
        }

        .tab-content {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .grid.md\\:grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .tab-btn {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }

            .tab-btn svg {
                width: 1rem;
                height: 1rem;
            }
        }
    </style>

@endsection
