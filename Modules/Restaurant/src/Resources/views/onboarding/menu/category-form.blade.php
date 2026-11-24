@extends($menu->layout)

@section('title', $category ? 'Edit category' : 'Add category')
@section('back_url', $menu->route('index', ['tab' => 'categories']))
@section('back_label', $menu->panel ? 'Back to menu management' : 'Back to menu details')
@section('heading', $category ? 'Edit category' : 'Add category')
@section('subheading', 'Categories are the sections of your menu, e.g. Starters, Main dishes or Desserts.')

@section('menu_content')
    @if ($errors->any())
        <div class="menu-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" class="menu-form"
          action="{{ $category ? $menu->route('categories.update', ['id' => $category->id]) : $menu->route('categories.store') }}">
        @csrf
        @if ($category) @method('PUT') @endif
        @if (!$category && old('return_to', request('return_to')) === 'item_create')
            <input type="hidden" name="return_to" value="item_create">
        @endif

        <section class="menu-section">
            <h3>Category details</h3>
            <p class="menu-section-sub">Customers browse your menu by these sections.</p>
            <div class="menu-fields">
                <div>
                    <label for="category-name" class="form-label">Name <span class="text-red-500">*</span></label>
                    <input id="category-name" name="name" class="input-field" maxlength="255" value="{{ old('name', $category->name ?? '') }}" placeholder="e.g. Starters" required>
                    @error('name')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="category-description" class="form-label">Description</label>
                    <textarea id="category-description" name="description" class="input-field" rows="3" maxlength="255">{{ old('description', $category->description ?? '') }}</textarea>
                    @error('description')<p class="error-message">{{ $message }}</p>@enderror
                </div>
                <div class="menu-grid-2">
                    <div>
                        <label for="category-status" class="form-label">Status</label>
                        <select id="category-status" name="status" class="input-field">
                            <option value="1" @selected(old('status', $category->status ?? 1) == 1)>Active</option>
                            <option value="0" @selected(old('status', $category->status ?? 1) == 0)>Inactive</option>
                        </select>
                        <p class="menu-help">Inactive categories are hidden from your menu.</p>
                    </div>
                    <div>
                        <label for="category-image" class="form-label">Category image</label>
                        <input id="category-image" type="file" name="category_image" accept="image/jpeg,image/png" class="menu-file">
                        <p class="menu-help">JPG or PNG, up to 2 MB.</p>
                        @if (is_array($categoryImage) && !empty($categoryImage['url']))
                            <img src="{{ $categoryImage['url'] }}" alt="Current category image" class="menu-current-img">
                        @endif
                        @error('category_image')<p class="error-message">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <div class="menu-actions">
            <a href="{{ $menu->route('index', ['tab' => 'categories']) }}" class="menu-btn menu-btn-ghost">Cancel</a>
            <button type="submit" class="menu-btn">{{ $category ? 'Update category' : 'Create category' }}</button>
        </div>
    </form>
@endsection
