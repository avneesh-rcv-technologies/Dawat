@php
    $servingTypes = app(\Modules\FoodManagements\Services\ServingOptionConfiguration::class)->all()['serve_types'];
    $options = $item->servingOptions;
    $availableOptions = $options->where('is_available', true);
    $taxRate = (float) ($item->tax_percentage ?? 0);
@endphp

@include('foodmanagements::food_items.partials.preview-styles')

<div class="food-preview mx-auto space-y-6">
    <a href="{{ $backUrl }}" class="food-preview-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M5 12h14" /></svg>
        Back to {{ $restaurant ? 'restaurant menu' : 'food items' }}
    </a>
    <header class="food-preview-header">
        <div class="food-preview-heading">
            <p class="food-preview-eyebrow">Food item details</p>
            <h2>{{ $item->name }}</h2>
            <p class="food-preview-restaurant">
                @if($restaurant)
                    <a href="{{ route('admin.restaurant.view', $restaurant->id) }}">{{ $restaurant->name }}</a>
                @else
                    Restaurant not assigned
                @endif
            </p>
            <div class="food-preview-statuses">
                <span class="food-preview-badge {{ $item->is_active ? 'is-active' : 'is-inactive' }}"><span aria-hidden="true">●</span> {{ $item->is_active ? 'Active' : 'Inactive' }}</span>
                <span class="food-preview-badge {{ $item->is_available ? 'is-available' : 'is-inactive' }}">{{ $item->is_available ? 'Available to order' : 'Unavailable' }}</span>
            </div>
        </div>
        @if ($canEdit)
            <a href="{{ $editUrl }}" class="food-preview-edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16 4 4 4M4 20l4-1L20 7a2.83 2.83 0 0 0-4-4L4 15l-1 6 5-2M13 5l4 4" /></svg>
                <span>Edit Item</span>
            </a>
        @endif
    </header>

    <div class="food-preview-overview">
        <section class="food-preview-gallery overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="food-preview-photo">
                @if ($images->isNotEmpty())
                    <img src="{{ $images->first()['url'] }}" alt="{{ $item->name }}" class="food-preview-main-image"
                        onerror="this.hidden=true;this.nextElementSibling.hidden=false;">
                @endif
                <div @if ($images->isNotEmpty()) hidden @endif class="p-8 text-center text-gray-400">
                    <span class="material-icons-outlined" style="font-size:48px" aria-hidden="true">restaurant</span>
                    <p class="mt-2 text-sm">No image uploaded</p>
                    @if ($canEdit)<a href="{{ $editUrl }}" class="food-preview-image-action">Add an item image</a>@endif
                </div>
            </div>
            @if ($images->count() > 1)
                <div class="grid grid-cols-3 gap-2 p-3">
                    @foreach ($images->skip(1) as $image)
                        <a href="{{ $image['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Open image of {{ $item->name }}">
                            <img src="{{ $image['url'] }}" alt="{{ $item->name }}" loading="lazy" class="h-20 w-full rounded-lg object-cover">
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="food-preview-details rounded-xl border border-gray-200 bg-white p-6">
            <h3 class="text-lg font-semibold text-gray-900">Item Details</h3>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $item->description ?: 'No description added.' }}</p>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Categories</h4>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @forelse ($item->categories as $category)
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">{{ $category->name }}</span>
                        @empty
                            <span class="text-sm text-gray-500">No category assigned</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Dietary Labels</h4>
                    <div class="mt-2 text-sm">
                        <div class="space-y-3">
                            @forelse ($dietaryLabels as $label)
                                <div>
                                    @include('foodmanagements::food_items.grid.dietary-label', ['label' => $label])
                                    <p class="mt-1 text-xs text-gray-500">Certification required: {{ ($label->is_certification_required ?? false) ? 'Yes' : 'No' }}</p>
                                </div>
                            @empty
                                <span class="text-gray-500">No dietary labels assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tax</h4>
                    <p class="mt-2 font-semibold text-gray-900">{{ number_format($taxRate, 2) }}%</p>
                    <p class="mt-1 text-xs text-gray-500">Applies to every serving option after any discount.</p>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Serving Options</h4>
                    <p class="mt-2 font-semibold text-gray-900">{{ $availableOptions->count() }} available / {{ $options->count() }} total</p>
                    <p class="mt-1 text-xs text-gray-500">Each size or package has its own price.</p>
                </div>
            </div>
            @if ($item->long_description)
                <div class="mt-6 border-t border-gray-100 pt-5">
                    <h4 class="font-medium text-gray-900">Additional Description</h4>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $item->long_description }}</p>
                </div>
            @endif
            <p class="mt-6 border-t border-gray-100 pt-4 text-xs text-gray-500">
                Created {{ $item->created_at?->format('M d, Y') ?? '—' }} · Updated {{ $item->updated_at?->format('M d, Y') ?? '—' }}
            </p>
        </section>
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-6">
        <div class="mb-5">
            <h3 class="text-lg font-semibold text-gray-900">Serving Options & Pricing</h3>
            <p class="mt-1 text-sm text-gray-500">Prices below show the item’s {{ number_format($taxRate, 2) }}% tax separately.</p>
        </div>
        <div class="food-preview-options">
            @forelse ($options as $option)
                @php
                    $effectivePrice = (float) ($option->discount_price ?? $option->price);
                    $taxAmount = round($effectivePrice * $taxRate / 100, 2);
                    $total = round($effectivePrice + $taxAmount, 2);
                    $isPerPerson = $option->serve_type === 'per_person';
                @endphp
                <article class="food-preview-option rounded-lg border border-gray-200 bg-gray-50 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $option->serving_name }}</h4>
                            <p class="mt-1 text-xs text-gray-500">{{ $servingTypes[$option->serve_type]['label'] ?? $option->serve_type }}</p>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs {{ $option->is_available ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">{{ $option->is_available ? 'Available' : 'Unavailable' }}</span>
                    </div>
                    <dl class="mt-5 space-y-3 text-sm">
                        @if ($isPerPerson)
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Minimum guests</dt><dd>{{ $option->min_guests }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Maximum guests</dt><dd>{{ $option->max_guests ?? 'No limit' }}</dd></div>
                        @else
                            @if ($option->quantity !== null && $option->unit)
                                <div class="flex justify-between gap-3"><dt class="text-gray-500">Size</dt><dd>{{ rtrim(rtrim(number_format((float) $option->quantity, 2, '.', ''), '0'), '.') }} {{ $option->unit }}</dd></div>
                            @endif
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Serves</dt><dd>{{ $option->serves }} people</dd></div>
                        @endif
                        @if ($option->discount_price !== null)
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Regular price</dt><dd class="food-preview-original-price">{{ $currency }} {{ number_format((float) $option->price, 2) }}</dd></div>
                        @endif
                        @if ($option->discount_price !== null)
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Offer price</dt><dd class="font-medium text-green-700">{{ $currency }} {{ number_format((float) $option->discount_price, 2) }}</dd></div>
                        @endif
                        @if ($option->discount_price === null)
                            <div class="flex justify-between gap-3"><dt class="text-gray-500">Price before tax</dt><dd>{{ $currency }} {{ number_format($effectivePrice, 2) }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-3"><dt class="text-gray-500">Tax ({{ number_format($taxRate, 2) }}%)</dt><dd>{{ $currency }} {{ number_format($taxAmount, 2) }}</dd></div>
                        <div class="food-preview-price-total"><dt>{{ $isPerPerson ? 'Total per guest' : 'Total per option' }}<small>Including tax</small></dt><dd>{{ $currency }} {{ number_format($total, 2) }}</dd></div>
                    </dl>
                    <p class="mt-3 text-xs text-gray-500">{{ $isPerPerson ? 'Prices shown per person. Multiply by the guest count for an order total.' : 'Prices shown per option ordered.' }}</p>
                    @if ($option->serve_type === 'package')
                        <p class="mt-2 text-xs text-gray-500">Fixed package for the stated number of people.</p>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 md:col-span-2 xl:col-span-3">
                    <p>No serving options have been configured for this item.</p>
                    <p class="mt-2">Base price before tax: {{ $currency }} {{ number_format((float) $item->sale_price, 2) }}</p>
                    @if ($canEdit)<a href="{{ $editUrl }}" class="mt-3 inline-block font-medium text-gray-900 underline">Add serving options</a>@endif
                </div>
            @endforelse
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6">
        <h3 class="mb-5 text-lg font-semibold text-gray-900">Features</h3>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @forelse ($featureGroups as $groupName => $features)
                <div class="rounded-lg border border-gray-200 p-5">
                    <h4 class="mb-4 font-semibold text-gray-900">{{ $groupName }}</h4>
                    <dl class="space-y-3 text-sm">
                        @foreach ($features as $feature)
                            <div class="flex flex-wrap justify-between gap-2"><dt class="text-gray-500">{{ $feature['name'] }}</dt><dd class="font-medium text-gray-900">{{ $feature['value'] !== null && $feature['value'] !== '' ? $feature['value'] : '—' }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            @empty
                <p class="text-sm text-gray-500">No features assigned to this item.</p>
            @endforelse
        </div>
    </section>
</div>
