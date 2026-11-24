<?php

namespace Modules\Restaurant\Http\Resources\V2;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Filemanager\Services\FileService;
use Modules\FoodManagements\Services\FoodItemFeatureData;

class FoodItemResource extends JsonResource
{
    public function __construct($resource, private array $eligibility, private array $configuration)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $images = collect(app(FileService::class)->getFiles('food_image', $this->id))->map(fn ($image) => [
            'id' => $image['file_link_id'] ?? $image['id'] ?? null,
            'url' => $image['url'] ?? null,
            'original_name' => $image['original_name'] ?? null,
        ])->values()->all();
        $features = FoodItemFeatureData::forItem($this->resource);
        $labelStatuses = collect($this->dietary_label_ids ?? [])->map(fn ($id) => [
            'id' => (string) $id,
            ...($this->eligibility[(string) $id] ?? [
                'name' => null, 'allowed' => false, 'required' => null,
                'is_certification_required' => null, 'is_uploaded' => false, 'is_approved' => false,
                'certification_type_id' => null, 'certificate_id' => null,
                'status' => 'unavailable', 'message' => 'This dietary label is no longer active.',
            ]),
        ])->values();

        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'name' => $this->name,
            'description' => $this->description,
            'features' => $features,
            'feature_mapping' => (object) FoodItemFeatureData::mapping($features),
            'category_id' => $this->categories->first()?->id,
            'categories' => $this->categories->map->only(['id', 'name'])->values(),
            'tax_percentage' => $this->tax_percentage,
            'dietary_label_ids' => array_map('strval', $this->dietary_label_ids ?? []),
            'dietary_labels' => $labelStatuses,
            'dietary_certificate_check' => [
                'can_save' => $labelStatuses->every(fn ($label) => $label['allowed']),
                'blocked_dietary_label_ids' => $labelStatuses->where('allowed', false)->pluck('id')->values(),
                'errors' => $labelStatuses->where('allowed', false)->map(fn ($label) =>
                    ($label['name'] ? $label['name'].': ' : '').$label['message'])->values(),
            ],
            'is_active' => $this->is_active,
            'is_available' => $this->is_available,
            'sale_price' => $this->sale_price,
            'list_price' => $this->list_price,
            'image' => $images[0] ?? null,
            'images' => $images,
            'serving_options' => $this->servingOptions->map(function ($option) {
                $type = $this->configuration['serve_types'][$option->serve_type] ?? [];
                $price = (float) ($option->discount_price ?? $option->price);
                $tax = round($price * (float) $this->tax_percentage / 100, 2);

                return [
                    ...$option->only([
                        'id', 'serve_type', 'serving_name', 'quantity', 'unit', 'serves',
                        'min_guests', 'max_guests', 'price', 'discount_price', 'is_available',
                    ]),
                    'serve_type_label' => $type['label'] ?? $option->serve_type,
                    'message' => $type['help'] ?? '',
                    'enabled_fields' => $type['enabled_fields'] ?? [],
                    'required_fields' => $type['required_fields'] ?? [],
                    'allowed_units' => $type['allowed_units'] ?? [],
                    'pricing_basis' => $type['pricing_basis'] ?? null,
                    'effective_price' => number_format($price, 2, '.', ''),
                    'tax_amount' => number_format($tax, 2, '.', ''),
                    'price_including_tax' => number_format($price + $tax, 2, '.', ''),
                ];
            })->values(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
