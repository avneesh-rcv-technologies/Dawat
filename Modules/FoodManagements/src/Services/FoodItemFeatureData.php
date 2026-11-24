<?php

namespace Modules\FoodManagements\Services;

use Modules\FoodManagements\Models\FoodItem;

class FoodItemFeatureData
{
    /** Editable feature_id => value(s) map, using the same shape as POST/PATCH. */
    public static function mapping(array $features): array
    {
        $mapping = [];
        foreach ($features as $feature) {
            if ($feature['status'] !== 'A' || $feature['values'] === []) {
                continue;
            }
            $first = $feature['values'][0];
            $value = match ($feature['feature_type']) {
                'S' => $first['variant_id'],
                'M' => array_values(array_filter(array_column($feature['values'], 'variant_id'), fn ($id) => $id !== null)),
                'C' => in_array(strtolower((string) $first['value']), ['y', 'yes', '1', 'true'], true),
                'T' => $first['value'],
                'O' => $first['value_int'] ?? $first['value'],
                default => null,
            };
            if ($value !== null) {
                $mapping[(string) $feature['feature_id']] = $value;
            }
        }

        return $mapping;
    }

    public const RELATIONS = [
        'featureValues.feature.description',
        'featureValues.variant.description',
    ];

    public static function forItem(FoodItem $item, bool $public = false): array
    {
        return $item->featureValues
            ->filter(function ($value) use ($item, $public) {
                $feature = $value->feature;

                return $feature && (int) $feature->restaurant_id === (int) $item->restaurant_id
                    && $feature->feature_type !== 'G'
                    && (! $public || ($feature->status === 'A' && $feature->display_on_product === 'Y'))
                    && (! $value->variant_id || ($value->variant
                        && (int) $value->variant->feature_id === (int) $feature->feature_id
                        && (! $public || $value->variant->status === 'A')));
            })
            ->groupBy('feature_id')
            ->map(function ($values) {
                $feature = $values->first()->feature;

                return [
                    'feature_id' => $feature->feature_id,
                    'name' => $feature->description?->name,
                    'feature_type' => $feature->feature_type,
                    'feature_style' => $feature->feature_style,
                    'filter_style' => $feature->filter_style,
                    'purpose' => $feature->purpose,
                    'status' => $feature->status,
                    'position' => $feature->position,
                    'values' => $values->map(fn ($value) => [
                        'variant_id' => $value->variant_id,
                        'variant_name' => $value->variant?->description?->description,
                        'value' => $value->value,
                        'value_int' => $value->value_int,
                    ])->values()->all(),
                ];
            })
            ->sortBy('position')->values()->all();
    }
}
