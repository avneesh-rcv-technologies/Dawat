@extends('backend::layouts.app')

@section('title', 'Food Category')

@section('breadcrumbs')
    @php
        $title = isset($category) ? 'Edit Food Category' : 'Create Food Category';
        
        // Check if coming from restaurant context
        $restaurantId = session('category_restaurant_id');
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
                ['label' => 'Food Category', 'url' => route('admin.foodmanagements.category.index')],
                ['label' => $title],
            ];
        }
    @endphp
    <x-breadcrumbs :breadcrumbs="$breadcrumbs" :name="$title" />
@endsection

@section('content')

    <form id="categoryForm" class="form-ajax" method="POST" enctype="multipart/form-data"
        action="@isset($category)
        {{ route('admin.foodmanagements.category.update', $category->id) }}
    @else
        {{ route('admin.foodmanagements.category.store') }}
    @endisset">

        @csrf
        @isset($category)
            @method('PUT')
        @endisset

        <input type="hidden" name="id" value="{{ $category->id ?? 0 }}">

        {{-- Restaurant (super admin only) --}}
        @if (!$is_restaurant_admin)
            @php
                // Check if we have a restaurant context from session
                $contextRestaurantId = $restaurant_context ?? null;
                $contextRestaurant = $contextRestaurantId ? \Modules\Restaurant\Models\Restaurant::find($contextRestaurantId) : null;
                $isContextLocked = !empty($contextRestaurant);
            @endphp

            @if ($isContextLocked)
                {{-- Restaurant is locked to context --}}
                <div class="mb-4">
                    <label class="custom-label">
                        Restaurant
                    </label>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm text-blue-800 font-medium">
                            {{ $contextRestaurant->name }}
                        </span>
                        <span class="ml-2 text-xs text-blue-600">(from restaurant context)</span>
                    </div>
                    <input type="hidden" name="restaurant_id" value="{{ $contextRestaurantId }}">
                </div>
            @else
                {{-- Normal dropdown for super admin without context --}}
                <div class="mb-4">
                    <label class="custom-label" for="restaurant_id">
                        Restaurant (Optional)
                    </label>
                    <select name="restaurant_id" id="restaurant_id" class="input-field">
                        <option value="">— No Restaurant —</option>
                        @foreach ($restaurants as $restaurant)
                            <option value="{{ $restaurant->id }}"
                                @selected(old('restaurant_id', $category->restaurant_id ?? '') == $restaurant->id)>
                                {{ $restaurant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        @endif

        {{-- Name --}}
        <div class="mb-4">
            <label class="custom-label" for="name">
                Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}"
                class="input-field @error('name') border-red-500 @enderror" onkeyup="generateSlug()">
            @error('name')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Slug --}}
        <div class="mb-4">
            <label class="custom-label" for="slug">
                Slug <span class="text-red-500">*</span>
            </label>
            <input type="text" name="slug" id="slug" readonly value="{{ old('slug', $category->slug ?? '') }}"
                class="input-field @error('slug') border-red-500 @enderror">
            @error('slug')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div class="mb-4">
            <label class="custom-label" for="description">Description</label>

            <textarea name="description" editor="false" id="description" rows="4"
                class="input-field @error('description') border-red-500 @enderror">{{ old('description', $category->description ?? '') }}</textarea>

            @error('description')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- Parent Category --}}
        <div class="mb-4">
            <label class="custom-label">
                Parent Category (Optional)
            </label>

            <select name="parent_id" class="input-field">
                <option value="">— No Parent (Category) —</option>

                @php
                    $currentCategory = $category ?? null;

                    // Filter out the current category from the parents list to prevent self-parenting
                    $parents = $parents->filter(function ($parent) use ($currentCategory) {
                        return !$currentCategory || $parent->id !== $currentCategory->id;
                    });
                @endphp
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? '') == $parent->id)>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
        </div>


        {{-- Food Type --}}
        <div class="mb-4">
            <label class="custom-label" for="food_type">
                Food Type <span class="text-red-500">*</span>
            </label>
            <select name="food_type" id="food_type" class="input-field @error('food_type') border-red-500 @enderror">
                <option value="">Select Food Type</option>
                <option value="all" @selected(old('food_type', $category->food_type ?? 'all') === 'all')>All</option>
                <option value="veg" @selected(old('food_type', $category->food_type ?? '') === 'veg')>Veg</option>
                <option value="non_veg" @selected(old('food_type', $category->food_type ?? '') === 'non_veg')>Non-Veg</option>
                <option value="vegan" @selected(old('food_type', $category->food_type ?? '') === 'vegan')>Vegan</option>
            </select>
            @error('food_type')
                <p class="text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>


        {{-- Status --}}
        <div class="mb-6">
            <label class="custom-label" for="status">Status</label>
            <select name="status" id="status" class="input-field @error('status') border-red-500 @enderror">
                <option value="1" @selected(old('status', $category->status ?? 1) == 1)>Active</option>
                <option value="0" @selected(old('status', $category->status ?? 1) == 0)>Inactive</option>
            </select>
        </div>

        {{-- Images Uploader --}}
        <div class="mb-4">
            @include('filemanager::components.file-uploader', [
                'object_type' => 'category_image',
                'object_id' => $category->id ?? 0,
                'name' => 'category_image',
                'accept' => 'image/jpeg,image/png',
            ])
        </div>

        {{-- Actions --}}
        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $cancelUrl = session('category_return_url') ?? route('admin.foodmanagements.category.index');
            @endphp
            <a href="{{ $cancelUrl }}" class="btn-cancel">
                Cancel
            </a>
            <button type="submit" name="button" class="btn-primary">
                @isset($category)
                    Update Category
                @else
                    Create Category
                @endisset
            </button>
        </div>

    </form>

    <script>
        // Slug generation function
        function generateSlug() {
            // If editing existing category, do not overwrite slug
            console.log("Hello");
            @if (isset($category))
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
@endsection
