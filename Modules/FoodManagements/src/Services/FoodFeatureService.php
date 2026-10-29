<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Repositories\FoodFeatureRepository;

class FoodFeatureService
{
    private const DEFAULT_LANG = 'en';
    private const TYPE_GROUP = 'G';
    private const PURPOSE_STYLES = [
        'find_products' => [
            ['feature_style' => 'text', 'filter_style' => 'checkbox', 'feature_type' => 'S'],
            ['feature_style' => 'checkbox', 'filter_style' => 'checkbox', 'feature_type' => 'C'],
            ['feature_style' => 'multiple_checkbox', 'filter_style' => 'checkbox', 'feature_type' => 'M'],
            ['feature_style' => 'text', 'filter_style' => 'date', 'feature_type' => 'D'],
            ['feature_style' => 'text', 'filter_style' => 'slider', 'feature_type' => 'N'],
            ['feature_style' => 'number', 'filter_style' => 'slider', 'feature_type' => 'O'],
            ['feature_style' => 'text', 'filter_style' => 'color', 'feature_type' => 'S'],
        ],
        'group_variation_catalog_item' => [
            ['feature_style' => 'dropdown', 'filter_style' => 'checkbox', 'feature_type' => 'S'],
            ['feature_style' => 'dropdown_images', 'filter_style' => 'checkbox', 'feature_type' => 'S'],
            ['feature_style' => 'dropdown_labels', 'filter_style' => 'checkbox', 'feature_type' => 'S'],
            ['feature_style' => 'dropdown', 'filter_style' => 'slider', 'feature_type' => 'N'],
            ['feature_style' => 'dropdown_labels', 'filter_style' => 'slider', 'feature_type' => 'N'],
            ['feature_style' => 'dropdown', 'filter_style' => 'color', 'feature_type' => 'S'],
            ['feature_style' => 'dropdown_images', 'filter_style' => 'color', 'feature_type' => 'S'],
            ['feature_style' => 'dropdown_labels', 'filter_style' => 'color', 'feature_type' => 'S'],
        ],
        'organize_catalog' => [
            ['feature_style' => 'brand', 'filter_style' => 'checkbox', 'feature_type' => 'E'],
        ],
        'describe_product' => [
            ['feature_style' => 'text', 'filter_style' => null, 'feature_type' => 'T'],
        ],
    ];

    private array $selectableTypes = ['S', 'N', 'E', 'M'];

    public function __construct(
        protected FoodFeatureRepository $repository
    ) {}

    public function list()
    {
        return $this->repository->all();
    }

    public function listForCategories(array $categoryIds = [])
    {
        return $this->repository->forCategories($categoryIds);
    }

    /**
     * Get feature groups with children + variants for the API.
     * Used by FoodFeaturesApiController.
     */
    public function getGroupsForApi(?int $categoryId = null, ?string $purpose = null): \Illuminate\Support\Collection
    {
        $query = \Modules\FoodManagements\Models\FoodFeature::with([
                'description',
                'children' => function ($q) use ($categoryId, $purpose) {
                    $q->where('status', 'A')->orderBy('position');

                    if ($purpose) {
                        $q->where('purpose', $purpose);
                    }

                    if ($categoryId) {
                        $q->where(function ($fq) use ($categoryId) {
                            $fq->whereDoesntHave('categories')
                               ->orWhereHas('categories', fn ($cq) =>
                                   $cq->where('food_categories.id', $categoryId)
                               );
                        });
                    }
                },
                'children.description',
                'children.variants' => fn ($q) => $q->where('status', true)->orderBy('position'),
                'children.variants.description',
            ])
            ->where('feature_type', self::TYPE_GROUP)
            ->where('status', "A")
            ->orderBy('position')
            ->get();
      
        return $query
            ->map(fn ($group) => [
                'group_id' => $group->feature_id,
                'name'     => $group->description->name ?? null,
                'position' => $group->position,
                'features' => $group->children->map(fn ($feature) => [
                    'feature_id'    => $feature->feature_id,
                    'name'          => $feature->description->name ?? null,
                    'feature_code'  => $feature->feature_code,
                    'feature_type'  => $feature->feature_type,
                    'feature_style' => $feature->feature_style,
                    'filter_style'  => $feature->filter_style,
                    'purpose'       => $feature->purpose,
                    'position'      => $feature->position,
                    'variants'      => $feature->variants->map(fn ($v) => [
                        'variant_id' => $v->variant_id,
                        'name'       => $v->description->name ?? null,
                        'position'   => $v->position,
                    ])->values(),
                ])->values(),
            ])
            ->filter(fn ($g) => collect($g['features'])->isNotEmpty())
            ->values();
    }

    public function create(array $data): FoodFeature
    {
        return DB::transaction(function () use ($data) {
            $featureData = $this->normalizeFeatureData($data);
            $feature = $this->repository->create($featureData);
            $this->upsertDescription($feature, $data);
            $this->syncCategories($feature, $data);
            $this->syncVariants($feature, $data);

            return $feature->fresh(['description', 'categories', 'variants.description', 'parent.description']);
        });
    }

    public function update(FoodFeature $feature, array $data): FoodFeature
    {
        return DB::transaction(function () use ($feature, $data) {
            $featureData = $this->normalizeFeatureData($data, $feature);
            $this->repository->update($feature, $featureData);
            $this->upsertDescription($feature, $data);
            $this->syncCategories($feature, $data);
            $this->syncVariants($feature, $data);

            return $feature->fresh(['description', 'categories', 'variants.description', 'parent.description']);
        });
    }

    public function delete(FoodFeature $feature): void
    {
        if ($feature->feature_type === self::TYPE_GROUP) {
            FoodFeature::where('parent_id', $feature->feature_id)->delete();
        }
        $this->repository->delete($feature);
    }

    public function bulkDelete(array $ids): void
    {
        $groupIds = FoodFeature::whereIn('feature_id', $ids)
            ->where('feature_type', self::TYPE_GROUP)
            ->pluck('feature_id')
            ->all();

        if (!empty($groupIds)) {
            FoodFeature::whereIn('parent_id', $groupIds)->delete();
        }

        $this->repository->bulkDelete($ids);
    }

    private function normalizeFeatureData(array $data, ?FoodFeature $feature = null): array
    {
        $purpose = $this->normalizeFeaturePurpose($data['purpose'] ?? $feature?->purpose ?? '');
        $featureStyle = trim((string) ($data['feature_style'] ?? $feature?->feature_style ?? ''));
        $filterStyle = trim((string) ($data['filter_style'] ?? $feature?->filter_style ?? ''));
        $featureTypeInput = trim((string) ($data['feature_type'] ?? ''));

        if ($featureTypeInput === '') {
            $featureTypeInput = $this->resolveFeatureTypeFromStyle($purpose, $featureStyle, $filterStyle, $feature?->feature_type ?? 'T');
        }

        $featureType = $this->normalizeFeatureType($featureTypeInput);
        $parentId = (int) ($data['parent_id'] ?? $feature?->parent_id ?? 0);

        if ($featureType === self::TYPE_GROUP) {
            $parentId = 0;
            $purpose = '';
            $featureStyle = '';
            $filterStyle = '';
        } else {
            [$featureType, $featureStyle, $filterStyle] = $this->normalizePurposeStyles($purpose, $featureType, $featureStyle, $filterStyle);
        }

        $normalized = [
            'feature_code'       => (string) ($data['feature_code'] ?? $feature?->feature_code ?? ''),
            'purpose'            => $purpose,
            'feature_style'      => $featureStyle,
            'filter_style'       => $filterStyle,
            'feature_type'       => $featureType,
            'parent_id'          => $parentId,
            'display_on_product' => $this->normalizeYesNo($data['display_on_product'] ?? $feature?->display_on_product ?? 'Y', 'Y'),
            'display_on_catalog' => $this->normalizeYesNo($data['display_on_catalog'] ?? $feature?->display_on_catalog ?? 'Y', 'Y'),
            'display_on_header'  => $this->normalizeYesNo($data['display_on_header'] ?? $feature?->display_on_header ?? 'N', 'N'),
            'status'             => $this->normalizeStatus($data['status'] ?? $feature?->status ?? 'A'),
            'position'           => (int) ($data['position'] ?? $feature?->position ?? 0),
            'comparison'         => $this->normalizeYesNo($data['comparison'] ?? $feature?->comparison ?? 'N', 'N'),
        ];

        $parent = $parentId ? FoodFeature::find($parentId) : null;
        if ($parent) {
            $normalized = $this->applyParentDisplayRules($normalized, $parent);
        }

        return $normalized;
    }

    private function upsertDescription(FoodFeature $feature, array $data): void
    {
        $feature->descriptions()->updateOrCreate(
            ['feature_id' => $feature->feature_id, 'lang_code' => self::DEFAULT_LANG],
            ['name' => (string) ($data['name'] ?? ''), 'description' => isset($data['description']) ? (string) $data['description'] : null]
        );
    }

    private function syncCategories(FoodFeature $feature, array $data): void
    {
        $categoryIds = $this->normalizeCategoryIds($data['category_ids'] ?? []);
        $parentId = (int) ($data['parent_id'] ?? $feature->parent_id ?? 0);

        if ($parentId) {
            $parent = FoodFeature::with('categories')->find($parentId);
            if ($parent && $parent->categories->isNotEmpty()) {
                $categoryIds = $parent->categories->pluck('id')->all();
            }
        }

        $feature->categories()->sync($categoryIds);

        if ($feature->feature_type === self::TYPE_GROUP) {
            FoodFeature::where('parent_id', $feature->feature_id)->get()
                ->each(fn ($child) => $child->categories()->sync($categoryIds));
        }
    }

    private function syncVariants(FoodFeature $feature, array $data): void
    {
        if (!$this->isSelectableType($feature->feature_type)) {
            $feature->variants()->delete();
            return;
        }

        $variants = Arr::get($data, 'variants', []);
        if (!is_array($variants)) {
            $variants = [];
        }

        $keepIds = [];

        foreach ($variants as $index => $variantData) {
            $name = trim((string) ($variantData['description'] ?? $variantData['name'] ?? ''));
            if ($name === '') continue;

            $variantPayload = [
                'position' => isset($variantData['position']) ? (int) $variantData['position'] : ($index + 1),
                'status'   => $this->normalizeStatus($variantData['status'] ?? 'A', 'A'),
            ];

            $variant = null;
            if (!empty($variantData['variant_id'])) {
                $variant = $feature->variants()->where('variant_id', (int) $variantData['variant_id'])->first();
            }

            $variant = $variant ? tap($variant)->update($variantPayload) : $feature->variants()->create($variantPayload);

            $variant->descriptions()->updateOrCreate(
                ['variant_id' => $variant->variant_id, 'lang_code' => self::DEFAULT_LANG],
                ['description' => $name]
            );

            $keepIds[] = $variant->variant_id;
        }

        $keepIds
            ? $feature->variants()->whereNotIn('variant_id', $keepIds)->delete()
            : $feature->variants()->delete();
    }

    private function normalizeCategoryIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }

    private function normalizeFeatureType(string $type): string
    {
        $type = strtoupper(trim($type));
        return $type === '' ? 'T' : substr($type, 0, 1);
    }

    private function normalizeFeaturePurpose($purpose): string
    {
        $purpose = trim((string) $purpose);
        return array_key_exists($purpose, self::PURPOSE_STYLES) ? $purpose : '';
    }

    private function normalizePurposeStyles(string $purpose, string $featureType, string $featureStyle, string $filterStyle): array
    {
        if (!isset(self::PURPOSE_STYLES[$purpose])) return [$featureType, $featureStyle, $filterStyle];

        $styles = self::PURPOSE_STYLES[$purpose];
        if (empty($styles)) return [$featureType, $featureStyle, $filterStyle];

        foreach ($styles as $style) {
            if ($style['feature_type'] === $featureType && $style['feature_style'] === $featureStyle && $this->normalizeStyleValue($style['filter_style']) === $filterStyle) {
                return [$featureType, $featureStyle, $filterStyle];
            }
        }

        foreach ($styles as $style) {
            if ($style['feature_type'] === $featureType) {
                return [$style['feature_type'], $style['feature_style'], $this->normalizeStyleValue($style['filter_style'])];
            }
        }

        $default = reset($styles);
        return [$default['feature_type'], $default['feature_style'], $this->normalizeStyleValue($default['filter_style'])];
    }

    private function resolveFeatureTypeFromStyle(string $purpose, string $featureStyle, string $filterStyle, string $default = 'T'): string
    {
        $featureStyle = $this->normalizeStyleValue($featureStyle);
        $filterStyle = $this->normalizeStyleValue($filterStyle);

        if ($purpose && isset(self::PURPOSE_STYLES[$purpose])) {
            foreach (self::PURPOSE_STYLES[$purpose] as $style) {
                if ($this->normalizeStyleValue($style['feature_style']) === $featureStyle && $this->normalizeStyleValue($style['filter_style']) === $filterStyle) {
                    return $style['feature_type'];
                }
            }
        }

        return match ($featureStyle) {
            'multiple_checkbox' => 'M',
            'checkbox' => 'C',
            'brand' => 'E',
            'number' => 'O',
            'dropdown', 'dropdown_images', 'dropdown_labels' => $filterStyle === 'slider' ? 'N' : 'S',
            'text' => match ($filterStyle) { 'date' => 'D', 'slider' => 'N', default => 'S' },
            default => $default,
        };
    }

    private function normalizeStyleValue($value): string
    {
        return $value === null ? '' : trim((string) $value);
    }

    private function normalizeYesNo($value, string $default = 'N'): string
    {
        if (is_string($value)) {
            $value = strtoupper(trim($value));
            if (in_array($value, ['Y', 'N'], true)) return $value;
            if (in_array($value, ['1', '0'], true)) return $value === '1' ? 'Y' : 'N';
        }
        if (is_bool($value) || is_numeric($value)) return $value ? 'Y' : 'N';
        return $default;
    }

    private function normalizeStatus($value, string $default = 'A'): string
    {
        if (is_string($value)) {
            $value = strtoupper(trim($value));
            if (in_array($value, ['A', 'H', 'D'], true)) return $value;
            if (in_array($value, ['1', '0'], true)) return $value === '1' ? 'A' : 'D';
        }
        if (is_bool($value) || is_numeric($value)) return $value ? 'A' : 'D';
        return $default;
    }

    private function isSelectableType(string $type): bool
    {
        return in_array(strtoupper($type), $this->selectableTypes, true);
    }

    private function applyParentDisplayRules(array $featureData, FoodFeature $parent): array
    {
        foreach (['display_on_product', 'display_on_catalog', 'display_on_header'] as $field) {
            if ($parent->{$field} === 'Y') $featureData[$field] = 'Y';
        }
        return $featureData;
    }
}
