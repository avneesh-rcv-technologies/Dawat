@extends($menu->layout)

@section('title', 'Food item details')
@section('heading', 'Food item details')
@section('menu_page_class', 'menu-food-detail')
@section('back_url', $menu->route('index', ['tab' => 'items']))
@section('back_label', 'Back to food items')
@section('subheading', 'Review the dish, serving prices and all related menu details.')

@section('menu_content')
    @php
        $gallery = collect($item->images)->filter(fn ($image) => !empty($image['url'])
            && !in_array($image['url'], ['/dawat.png', asset('dawat.png')], true))->values();
        $foodTypes = ['veg' => 'Vegetarian', 'non_veg' => 'Non-vegetarian', 'vegan' => 'Vegan', 'all' => 'All food types'];
        $prices = $item->servingOptions->map(fn ($option) => $option->discount_price ?? $option->price)->filter(fn ($price) => $price !== null);
        $fromPrice = $prices->isNotEmpty() ? $prices->min() : $item->sale_price;
    @endphp

    <section class="menu-panel food-detail-hero" aria-labelledby="food-item-title">
        <div class="food-detail-cover">
            @if ($gallery->isNotEmpty())
                <img src="{{ $gallery->first()['url'] }}" alt="{{ $item->name }}">
            @else
                <div class="food-detail-placeholder"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M4 3v7a3 3 0 0 0 6 0V3M7 3v19M20 22V3c-4 2-5 8 0 10"/></svg><span>No image added</span></div>
            @endif
        </div>
        <div class="food-detail-intro">
            <p class="food-detail-eyebrow"><span>{{ $restaurant->name }}</span><span class="food-detail-item-id">Item #{{ $item->id }}</span></p>
            <h2 id="food-item-title">{{ $item->name }}</h2>
            <div class="food-detail-chips">
                <span class="menu-chip {{ $item->is_active ? 'is-on' : 'is-off' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                <span class="menu-chip {{ $item->is_available ? 'is-on' : 'is-off' }}">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span>
                <span class="menu-chip {{ $item->in_stock ? 'is-on' : 'is-off' }}">{{ $item->in_stock ? 'In stock' : 'Out of stock' }}</span>
                @if ($item->food_type)<span class="menu-chip">{{ $foodTypes[$item->food_type] ?? $item->food_type }}</span>@endif
            </div>
            <p class="food-detail-description">{{ $item->description ?: 'No description added.' }}</p>
            <div class="food-detail-summary">
                <span>{{ $item->servingOptions->count() }} {{ \Illuminate\Support\Str::plural('serving option', $item->servingOptions->count()) }}</span>
                <span>{{ count($itemFeatures) }} {{ \Illuminate\Support\Str::plural('feature', count($itemFeatures)) }}</span>
                <span>{{ $item->categories->count() }} {{ \Illuminate\Support\Str::plural('category', $item->categories->count()) }}</span>
            </div>
        </div>
        <div class="food-detail-price-card">
            <span class="food-detail-label">{{ $prices->isNotEmpty() ? 'Serving price from' : 'Sale price' }}</span>
            <strong class="food-detail-price">{{ $fromPrice !== null ? number_format((float) $fromPrice, 2) : 'Not set' }}</strong>
            <span class="food-detail-tax">Item tax · {{ number_format((float) $item->tax_percentage, 2) }}%</span>
            <a href="{{ $menu->route('items.edit', ['id' => $item->id]) }}" class="menu-btn food-detail-edit">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Edit food item
            </a>
        </div>
    </section>

    <nav class="food-detail-nav" aria-label="Food item details sections">
        <a href="#item-overview">Overview</a>
        <a href="#item-servings">Serving & pricing <span>{{ $item->servingOptions->count() }}</span></a>
        <a href="#item-features">Features <span>{{ count($itemFeatures) }}</span></a>
        <a href="#item-gallery">Images <span>{{ $gallery->count() }}</span></a>
    </nav>

    <div class="food-detail-layout">
        <div class="food-detail-main">
            <section id="item-overview" class="menu-panel food-detail-section">
                <div class="menu-toolbar"><div><h2>Item overview</h2><p>Descriptions and menu classification.</p></div></div>
                <h3>Detailed description</h3>
                <p class="food-detail-description">{{ trim(strip_tags($item->long_description ?? '')) ?: 'No detailed description added.' }}</p>
                <div class="food-detail-overview-grid">
                    <div class="food-detail-category-group">
                        <h3>Categories <span class="menu-chip">{{ $item->categories->count() }}</span></h3>
                        @forelse ($item->categories as $category)
                            <div class="food-detail-related">
                                <div><strong>{{ $category->name }}</strong>@if ($category->parent)<small>Under {{ $category->parent->name }}</small>@endif @if ($category->description)<small>{{ $category->description }}</small>@endif</div>
                                <span class="menu-chip {{ $category->status ? 'is-on' : 'is-off' }}">{{ $category->status ? 'Active' : 'Inactive' }}</span>
                            </div>
                        @empty
                            <p class="food-detail-empty">No categories assigned.</p>
                        @endforelse
                    </div>
                    <div class="food-detail-label-group">
                        <h3>Dietary labels</h3>
                        <div class="food-detail-chips">
                            @forelse ($dietaryLabels as $label)
                                <span class="menu-chip">{{ $label->name }}{{ $label->is_active ? '' : ' (Inactive)' }}</span>
                            @empty
                                <span class="food-detail-empty">No dietary labels assigned.</span>
                            @endforelse
                        </div>
                        <h3>Tags</h3>
                        <div class="food-detail-chips">
                            @forelse ($item->tags as $tag)<span class="menu-chip">{{ $tag->name }}</span>@empty<span class="food-detail-empty">No tags assigned.</span>@endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section id="item-servings" class="menu-panel food-detail-section">
                <div class="menu-toolbar"><div><h2>Serving & pricing <span class="menu-chip">{{ $item->servingOptions->count() }}</span></h2><p>Portions, guest counts and prices for each serving option.</p></div></div>
                @if ($item->servingOptions->isNotEmpty())
                    <div class="food-detail-table-wrap" role="region" aria-label="Serving options" tabindex="0">
                        <table class="food-detail-table food-detail-serving-table">
                            <thead><tr><th scope="col">Serving option</th><th scope="col">Quantity</th><th scope="col">Serves / guests</th><th scope="col">Price</th><th scope="col">Discount price</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                @foreach ($item->servingOptions as $option)
                                    <tr>
                                        <td><strong>{{ $option->serving_name }}</strong><small>{{ \Modules\FoodManagements\Models\FoodItemServingOption::SERVE_TYPES[$option->serve_type] ?? $option->serve_type }}</small></td>
                                        <td>{{ $option->quantity !== null ? number_format((float) $option->quantity, 2) . ' ' . $option->unit : '—' }}</td>
                                        <td>@if ($option->min_guests !== null || $option->max_guests !== null){{ $option->min_guests ?? '—' }} – {{ $option->max_guests ?? 'No limit' }} guests @elseif ($option->serves !== null){{ $option->serves }} people @else — @endif</td>
                                        <td>{{ number_format((float) $option->price, 2) }}</td>
                                        <td>{{ $option->discount_price !== null ? number_format((float) $option->discount_price, 2) : '—' }}</td>
                                        <td><span class="menu-chip {{ $option->is_available ? 'is-on' : 'is-off' }}">{{ $option->is_available ? 'Available' : 'Unavailable' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="food-detail-empty">No serving options added. The item uses its base pricing.</p>
                @endif
            </section>

            <section id="item-features" class="menu-panel food-detail-section">
                <div class="menu-toolbar"><div><h2>Features <span class="menu-chip">{{ count($itemFeatures) }}</span></h2><p>Selected characteristics and values for this dish.</p></div></div>
                @if ($itemFeatures)
                    <div class="food-detail-table-wrap" role="region" aria-label="Item features" tabindex="0">
                        <table class="food-detail-table">
                            <thead><tr><th scope="col">Feature</th><th scope="col">Type</th><th scope="col">Selected values</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                @foreach ($itemFeatures as $feature)
                                    <tr>
                                        <td><strong>{{ $feature['name'] ?: 'Feature #' . $feature['feature_id'] }}</strong></td>
                                        <td>{{ \Modules\Restaurant\Http\Controllers\OnboardingMenuController::FEATURE_TYPES[$feature['feature_type']]['label'] ?? $feature['feature_type'] }}</td>
                                        <td><div class="food-detail-chips">@foreach ($feature['values'] as $value)
                                            @php
                                                $displayValue = match ($feature['feature_type']) {
                                                    'C' => in_array(strtolower((string) $value['value']), ['y', 'yes', '1', 'true'], true) ? 'Yes' : 'No',
                                                    'O' => $value['value_int'] ?? $value['value'],
                                                    default => $value['variant_name'] ?? $value['value'] ?? $value['value_int'],
                                                };
                                            @endphp
                                            <span class="menu-chip">{{ $displayValue !== null && $displayValue !== '' ? $displayValue : '—' }}</span>
                                        @endforeach</div></td>
                                        <td><span class="menu-chip {{ $feature['status'] === 'A' ? 'is-on' : 'is-off' }}">{{ $feature['status'] === 'A' ? 'Active' : 'Hidden' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="food-detail-empty">No features assigned to this item.</p>
                @endif
            </section>

            <section id="item-gallery" class="menu-panel food-detail-section">
                <div class="menu-toolbar"><div><h2>Item images <span class="menu-chip">{{ $gallery->count() }}</span></h2><p>Open an image to view it at full size.</p></div></div>
                @if ($gallery->isNotEmpty())
                    <div class="food-detail-gallery">@foreach ($gallery as $image)<a href="{{ $image['url'] }}" target="_blank" rel="noopener noreferrer"><img src="{{ $image['url'] }}" alt="{{ $item->name }} — image {{ $loop->iteration }}" loading="lazy"></a>@endforeach</div>
                @else
                    <p class="food-detail-empty">No images uploaded for this item.</p>
                @endif
            </section>
        </div>
        <aside class="food-detail-sidebar" aria-label="Item information">
            <section class="menu-panel food-detail-section">
                <div class="menu-toolbar"><div><h2>Pricing & stock</h2></div></div>
                <dl class="food-detail-facts">
                    <div><dt>Sale price</dt><dd>{{ $item->sale_price !== null ? number_format((float) $item->sale_price, 2) : '—' }}</dd></div>
                    <div><dt>List price</dt><dd>{{ $item->list_price !== null ? number_format((float) $item->list_price, 2) : '—' }}</dd></div>
                    <div><dt>Cost price</dt><dd>{{ $item->cost_price !== null ? number_format((float) $item->cost_price, 2) : '—' }}</dd></div>
                    <div><dt>Tax</dt><dd>{{ number_format((float) $item->tax_percentage, 2) }}%</dd></div>
                    <div><dt>Quantity type</dt><dd>{{ $item->quantity_type ? ucfirst($item->quantity_type) : '—' }}</dd></div>
                    <div><dt>{{ $item->quantity_label }}</dt><dd>{{ $item->quantity ?? '—' }}</dd></div>
                </dl>
            </section>
            <section class="menu-panel food-detail-section">
                <div class="menu-toolbar"><div><h2>Record details</h2></div></div>
                <dl class="food-detail-facts">
                    <div><dt>Item name</dt><dd>{{ $item->name }}</dd></div>
                    <div><dt>Slug</dt><dd>{{ $item->slug ?: '—' }}</dd></div>
                    <div><dt>Created</dt><dd>{{ $item->created_at?->format('d M Y, H:i') ?? '—' }}</dd></div>
                    <div><dt>Last updated</dt><dd>{{ $item->updated_at?->format('d M Y, H:i') ?? '—' }}</dd></div>
                </dl>
            </section>
        </aside>
    </div>
@endsection
