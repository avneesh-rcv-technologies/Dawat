@php($servingTypes = app(\Modules\FoodManagements\Services\ServingOptionConfiguration::class)->all()['serve_types'])
@if ($options->isNotEmpty())
    <div class="space-y-2" style="min-width:170px;max-width:240px">
        @foreach ($options->take(2) as $option)
            <div>
                <span class="font-medium">{{ $option->serving_name }}</span>
                <p class="text-xs text-gray-500">
                    {{ $servingTypes[$option->serve_type]['label'] ?? $option->serve_type }}
                    @if ($option->serve_type === 'per_person')
                        · {{ $option->min_guests }}{{ $option->max_guests ? '–'.$option->max_guests : '+' }} guests
                    @elseif ($option->serves)
                        · Serves {{ $option->serves }}
                    @endif
                    @if (!$option->is_available) · Unavailable @endif
                </p>
            </div>
        @endforeach
        @if ($options->count() > 2)
            <p class="text-xs text-gray-500">+{{ $options->count() - 2 }} more options</p>
        @endif
    </div>
@else
    <span class="text-gray-500">No serving options</span>
@endif
