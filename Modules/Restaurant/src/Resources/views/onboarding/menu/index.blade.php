@extends($menu->layout)

@section('title', $menu->panel ? 'Menu Management' : 'Menu details')
@section('heading')
    @if ($menu->panel)
        Menu Management
    @else
        Menu details <span class="menu-optional-tag">Optional</span>
    @endif
@endsection
@section('subheading')
    @if ($menu->panel)
        Manage the food items, categories and features on {{ $menu->restaurant->name }}'s menu.
    @else
        Add your food items, organise them into categories and describe them with features. This step is optional — you can finish it now or after your store is approved.
    @endif
@endsection

@section('menu_content')
    @php
        $tabs = [
            'items' => ['Food items', $foodItems->total()],
            'categories' => ['Categories', $categoryList->count()],
            'features' => ['Features', null],
        ];
    @endphp

    <nav class="menu-tabs" aria-label="Menu sections">
        @foreach ($tabs as $key => [$label, $count])
            <a href="{{ $menu->route('index', ['tab' => $key]) }}"
               class="menu-tab {{ $tab === $key ? 'is-active' : '' }}" @if ($tab === $key) aria-current="page" @endif>
                <span>{{ $label }}</span>
                @if ($count !== null)<span class="menu-tab-count">{{ $count }}</span>@endif
            </a>
        @endforeach
    </nav>

    <div class="menu-panel">
        {{-- ===================== FOOD ITEMS ===================== --}}
        @if ($tab === 'items')
            <div class="menu-toolbar">
                <div>
                    <h2>Food items</h2>
                    <p>Dishes customers will see on your menu.</p>
                </div>
                @if ($categoryList->isNotEmpty())
                    <a href="{{ $menu->route('items.create') }}" class="menu-btn">+ Add food item</a>
                @endif
            </div>

            @if ($categoryList->isEmpty())
                <div class="menu-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#52525B" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
                    <h3>Create a category first</h3>
                    <p>Every food item belongs to a category, e.g. Starters or Main dishes.</p>
                    <a href="{{ $menu->route('categories.create') }}" class="menu-btn">+ Add category</a>
                </div>
            @else
                <form method="GET" action="{{ $menu->route('index') }}" class="menu-search" style="margin-bottom: 18px;">
                    <input type="hidden" name="tab" value="items">
                    <input type="search" name="q" value="{{ $search }}" class="input-field" placeholder="Search food items" aria-label="Search food items">
                    <button type="submit" class="menu-btn menu-btn-ghost">Search</button>
                </form>

                @if ($foodItems->isEmpty())
                    <div class="menu-empty">
                        <h3>{{ $search !== '' ? 'No food items match your search' : 'No food items yet' }}</h3>
                        <p>{{ $search !== '' ? 'Try a different name.' : 'Add your first dish to start building your menu.' }}</p>
                        @if ($search === '')
                            <a href="{{ $menu->route('items.create') }}" class="menu-btn">+ Add food item</a>
                        @endif
                    </div>
                @else
                    <div class="menu-list">
                        @foreach ($foodItems as $foodItem)
                            @php
                                $image = $foodItem->image;
                                $hasImage = $image && !empty($image['url']) && !in_array($image['url'], ['/dawat.png', asset('dawat.png')], true);
                                $price = $foodItem->servingOptions->first();
                            @endphp
                            <article class="menu-row">
                                @if ($hasImage)
                                    <img src="{{ $image['url'] }}" alt="" class="menu-thumb">
                                @else
                                    <div class="menu-thumb" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/></svg></div>
                                @endif
                                <div class="menu-row-body">
                                    <h3 class="menu-row-title"><a href="{{ $menu->route('items.show', ['id' => $foodItem->id]) }}" class="menu-item-title-link">{{ $foodItem->name }}</a></h3>
                                    <p class="menu-row-meta">
                                        <span>{{ $foodItem->categories->pluck('name')->implode(', ') ?: 'No category' }}</span>
                                        @if ($price)
                                            <span>{{ number_format((float) ($price->discount_price ?? $price->price), 2) }} · {{ $price->serving_name }}</span>
                                        @endif
                                        <span>{{ $foodItem->servingOptions->count() }} serving {{ \Illuminate\Support\Str::plural('option', $foodItem->servingOptions->count()) }}</span>
                                        <span>{{ $foodItem->feature_values_count }} {{ \Illuminate\Support\Str::plural('feature', $foodItem->feature_values_count) }}</span>
                                    </p>
                                    <p class="menu-row-meta">
                                        <span class="menu-chip {{ $foodItem->is_active ? 'is-on' : '' }}">{{ $foodItem->is_active ? 'Active' : 'Inactive' }}</span>
                                        <span class="menu-chip {{ $foodItem->is_available ? 'is-on' : 'is-off' }}">{{ $foodItem->is_available ? 'Available' : 'Unavailable' }}</span>
                                    </p>
                                </div>
                                <div class="menu-row-actions">
                                    <a href="{{ $menu->route('items.show', ['id' => $foodItem->id]) }}" class="menu-icon-btn" title="View details" aria-label="View {{ $foodItem->name }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                    <a href="{{ $menu->route('items.edit', ['id' => $foodItem->id]) }}" class="menu-icon-btn" title="Edit" aria-label="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                                    <form method="POST" action="{{ $menu->route('items.destroy', ['id' => $foodItem->id]) }}" data-confirm-tone="danger" data-confirm-button="Delete" data-confirm="Delete “{{ $foodItem->name }}”? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="menu-icon-btn is-danger" title="Delete" aria-label="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($foodItems->hasPages())
                        <div class="menu-pagination">
                            <a href="{{ $foodItems->previousPageUrl() ?? '#' }}" class="menu-btn menu-btn-ghost menu-btn-sm" @if ($foodItems->onFirstPage()) aria-disabled="true" @endif>← Previous</a>
                            <span>Page {{ $foodItems->currentPage() }} of {{ $foodItems->lastPage() }}</span>
                            <a href="{{ $foodItems->nextPageUrl() ?? '#' }}" class="menu-btn menu-btn-ghost menu-btn-sm" @if (!$foodItems->hasMorePages()) aria-disabled="true" @endif>Next →</a>
                        </div>
                    @endif
                @endif
            @endif

        {{-- ===================== CATEGORIES ===================== --}}
        @elseif ($tab === 'categories')
            <div class="menu-toolbar">
                <div>
                    <h2>Categories</h2>
                    <p>Menu sections such as Starters, Main dishes or Desserts.</p>
                </div>
                <a href="{{ $menu->route('categories.create') }}" class="menu-btn">+ Add category</a>
            </div>

            @if ($categoryList->isEmpty())
                <div class="menu-empty">
                    <h3>No categories yet</h3>
                    <p>Create a category, then add food items to it.</p>
                </div>
            @else
                <div class="menu-list">
                    @foreach ($categoryList as $category)
                        @php $categoryImage = fn_get_image('category_image', $category->id); @endphp
                        <article class="menu-row">
                            @if (is_array($categoryImage) && !empty($categoryImage['url']))
                                <img src="{{ $categoryImage['url'] }}" alt="" class="menu-thumb">
                            @else
                                <div class="menu-thumb" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18M3 12h18M3 17h18"/></svg></div>
                            @endif
                            <div class="menu-row-body">
                                <h3 class="menu-row-title">{{ $category->name }}</h3>
                                <p class="menu-row-meta">
                                    <span>{{ $category->food_items_count }} {{ \Illuminate\Support\Str::plural('item', $category->food_items_count) }}</span>
                                    @if ($category->description)<span>{{ \Illuminate\Support\Str::limit($category->description, 80) }}</span>@endif
                                </p>
                                <p class="menu-row-meta">
                                    <span class="menu-chip {{ $category->status ? 'is-on' : '' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span>
                                </p>
                            </div>
                            <div class="menu-row-actions">
                                <a href="{{ $menu->route('categories.edit', ['id' => $category->id]) }}" class="menu-icon-btn" title="Edit" aria-label="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                                <form method="POST" action="{{ $menu->route('categories.destroy', ['id' => $category->id]) }}" data-confirm-tone="danger" data-confirm-button="Delete" data-confirm="Delete the “{{ $category->name }}” category?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="menu-icon-btn is-danger" title="Delete" aria-label="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

        {{-- ===================== FEATURES ===================== --}}
        @else
            <div class="menu-toolbar">
                <div>
                    <h2>Your features</h2>
                    <p>Create features such as spice level or portion size. They are only used on your menu.</p>
                </div>
                <a href="{{ $menu->route('features.create') }}" class="menu-btn">+ Add feature</a>
            </div>

            @if ($featureList->isEmpty())
                <div class="menu-empty">
                    <h3>No features yet</h3>
                    <p>Add your first feature, e.g. “Spice level” with Mild, Medium and Hot.</p>
                </div>
            @else
                <div class="menu-list">
                    @foreach ($featureList as $feature)
                        @php $typeLabel = \Modules\Restaurant\Http\Controllers\OnboardingMenuController::FEATURE_TYPES[$feature->feature_type]['label'] ?? $feature->feature_type; @endphp
                        <article class="menu-row">
                            <div class="menu-row-body">
                                <h3 class="menu-row-title">{{ $feature->description->name ?? 'Feature' }}</h3>
                                <p class="menu-row-meta">
                                    <span>{{ $typeLabel }}</span>
                                    @if ($feature->variants->isNotEmpty())
                                        <span>{{ $feature->variants->map(fn ($variant) => $variant->description->description ?? '')->filter()->implode(' · ') }}</span>
                                    @endif
                                    <span>{{ $feature->items_count ? 'Used on ' . $feature->items_count . ' ' . \Illuminate\Support\Str::plural('item', $feature->items_count) : 'Not used on any item yet' }}</span>
                                </p>
                                <p class="menu-row-meta">
                                    <span class="menu-chip {{ $feature->status === 'A' ? 'is-on' : '' }}">{{ $feature->status === 'A' ? 'Active' : 'Hidden' }}</span>
                                </p>
                            </div>
                            <div class="menu-row-actions">
                                <a href="{{ $menu->route('features.edit', ['id' => $feature->feature_id]) }}" class="menu-icon-btn" title="Edit" aria-label="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></a>
                                <form method="POST" action="{{ $menu->route('features.destroy', ['id' => $feature->feature_id]) }}" data-confirm-tone="danger" data-confirm-button="Delete" data-confirm="Delete the “{{ $feature->description->name ?? 'feature' }}” feature? It will be removed from your food items.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="menu-icon-btn is-danger" title="Delete" aria-label="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg></button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection
