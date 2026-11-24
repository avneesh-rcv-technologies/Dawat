<div class="flex flex-wrap gap-3">
    @forelse ($labels as $label)
        @include('foodmanagements::food_items.grid.dietary-label', ['label' => $label])
    @empty
        <span>—</span>
    @endforelse
</div>
