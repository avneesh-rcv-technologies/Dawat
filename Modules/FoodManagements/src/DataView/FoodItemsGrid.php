<?php

namespace Modules\FoodManagements\DataView;

use Illuminate\Support\Facades\DB;
use Modules\DataView\DataGrid;
use Modules\DataView\ColumnTypes\Decimal;
use Modules\Filemanager\Models\FileLink;
use Modules\Filemanager\Services\FileService;
use Modules\FoodManagements\Models\FoodItemServingOption;

class FoodItemsGrid extends DataGrid
{
    protected $primaryColumn = 'id';
    protected $itemsPerPage = 10;
    protected $sortOrder = 'desc';
    protected array $details = [];
    protected ?int $contextRestaurantId = null;

    public function forRestaurant(int $restaurantId): static
    {
        if ($restaurantId < 1) {
            throw new \InvalidArgumentException('A valid restaurant ID is required.');
        }
        $this->contextRestaurantId = $restaurantId;

        return $this;
    }

    protected function filterOptions(string $type): array
    {
        if ($type === 'category') {
            $query = DB::table('food_categories')->orderBy('name');
            if (($restaurantId = $this->restaurantId()) !== null) {
                $query->where('restaurant_id', $restaurantId);
            }
            return $query->get(['id', 'name'])->map(fn ($row) => ['label' => $row->name, 'value' => $row->id])->all();
        }

        return DB::table('dietary_labels')->orderBy('display_order')->get(['id', 'name'])
            ->map(fn ($row) => ['label' => $row->name, 'value' => (string) $row->id])->all();
    }

    protected function processRequestedFilters(array $requestedFilters)
    {
        if (isset($requestedFilters['tax_percentage']) && $requestedFilters['tax_percentage'] !== '') {
            $requestedFilters['tax_percentage'] = $this->numberFilterValues((array) $requestedFilters['tax_percentage'], 'tax_percentage');
        }
        foreach (['category', 'dietary_label', 'serve_type', 'food_image', 'sale_price'] as $filter) {
            $values = array_values(array_filter((array) ($requestedFilters[$filter] ?? []),
                fn ($value) => is_scalar($value) && trim((string) $value) !== ''
            ));
            unset($requestedFilters[$filter]);
            if ($values === []) {
                continue;
            }

            if ($filter === 'dietary_label') {
                $this->queryBuilder->where(function ($scope) use ($values) {
                    foreach ($values as $value) {
                        $scope->orWhereJsonContains('food_items.dietary_label_ids', (string) $value);
                    }
                });
            } elseif ($filter === 'category') {
                $this->queryBuilder->whereExists(function ($query) use ($values) {
                    $query->selectRaw('1')->from('food_category_items')
                        ->whereColumn('food_category_items.food_item_id', 'food_items.id')
                        ->whereIn('food_category_items.food_category_id', $values);
                });
            } elseif ($filter === 'serve_type') {
                $this->queryBuilder->whereExists(function ($query) use ($values) {
                    $query->selectRaw('1')->from('food_item_serving_options')
                        ->whereColumn('food_item_serving_options.food_item_id', 'food_items.id')
                        ->whereIn('serve_type', $values);
                });
            } elseif ($filter === 'food_image') {
                $values = array_intersect($values, ['with_image', 'without_image']);
                if (count(array_unique($values)) === 1) {
                    $method = reset($values) === 'with_image' ? 'whereExists' : 'whereNotExists';
                    $this->queryBuilder->$method(function ($query) {
                        $query->selectRaw('1')->from('file_links')
                            ->join('files', 'files.id', '=', 'file_links.file_id')
                            ->whereColumn('file_links.object_id', 'food_items.id')
                            ->where('file_links.object_type', 'food_image')->where('files.is_image', true);
                    });
                }
            } else {
                $values = $this->numberFilterValues($values, 'sale_price');
                $this->queryBuilder->where(function ($scope) use ($values) {
                    $scope->whereExists(function ($query) use ($values) {
                        $query->selectRaw('1')->from('food_item_serving_options as price_options')
                            ->whereColumn('price_options.food_item_id', 'food_items.id')
                            ->where('price_options.is_available', true);
                        $price = new Decimal(['index' => 'price', 'label' => 'Price', 'type' => 'decimal']);
                        $price->setColumnName(DB::raw('COALESCE(price_options.discount_price, price_options.price)'));
                        $price->processFilter($query, array_map('strval', $values));
                    })->orWhere(function ($legacy) use ($values) {
                        $legacy->whereNotExists(function ($query) {
                            $query->selectRaw('1')->from('food_item_serving_options')
                                ->whereColumn('food_item_serving_options.food_item_id', 'food_items.id');
                        });
                        $price = new Decimal(['index' => 'sale_price', 'label' => 'Price', 'type' => 'decimal']);
                        $price->setColumnName('food_items.sale_price');
                        $price->processFilter($legacy, array_map('strval', $values));
                    });
                });
            }
        }

        return parent::processRequestedFilters($requestedFilters);
    }

    protected function numberFilterValues(array $values, string $field): array
    {
        $values = array_values(array_filter($values, fn ($value) => $value !== null && $value !== ''));
        $values = array_map(fn ($value) => is_scalar($value) ? trim((string) $value) : $value, $values);
        validator(['filters' => [$field => $values]], ['filters.'.$field.'.*' => [
            'required', 'string', 'regex:/^(?:(?:>=|<=|>|<|=)\s*)?\d+(?:\.\d{1,2})?$|^\d+(?:\.\d{1,2})?\s*-\s*\d+(?:\.\d{1,2})?$/',
        ]], ['filters.'.$field.'.*.regex' => 'Enter a value such as 20, 10-50, or >=20.'])->validate();

        return $values;
    }

    public function prepareQueryBuilder()
    {
        $query = DB::table('food_items')->select('food_items.*');
        $restaurantId = $this->restaurantId();

        if ($restaurantId !== null) {
            $query->where('food_items.restaurant_id', $restaurantId);
        }

        return $query;
    }

    protected function restaurantId(): ?int
    {
        if ($this->contextRestaurantId !== null) {
            return $this->contextRestaurantId;
        }
        $admin = auth('admin')->user();
        $ownedId = $admin
            ? \Modules\Restaurant\Models\Restaurant::where('admin_id', $admin->id)->value('id')
            : null;
        $id = $ownedId;

        return $id !== null ? (int) $id : null;
    }

    protected function formatRecords($records): mixed
    {
        $this->details = [];
        $ids = collect($records)->pluck('id')->all();
        if ($ids === []) {
            return parent::formatRecords($records);
        }

        // Load related data once for the current page rather than once per cell.
        $categories = DB::table('food_category_items')
            ->join('food_categories', 'food_categories.id', '=', 'food_category_items.food_category_id')
            ->whereIn('food_category_items.food_item_id', $ids)
            ->orderBy('food_categories.name')
            ->get(['food_category_items.food_item_id', 'food_categories.name'])->groupBy('food_item_id');
        $itemLabelIds = collect($records)->mapWithKeys(function ($row) {
            $ids = $row->dietary_label_ids ?? [];
            return [$row->id => is_array($ids) ? $ids : (json_decode($ids, true, 512, JSON_THROW_ON_ERROR) ?? [])];
        });
        $labelIds = $itemLabelIds->flatten()->unique()->values()->all();
        $labels = $labelIds ? DB::table('dietary_labels')->whereIn('id', $labelIds)->get()->keyBy('id') : collect();
        $options = FoodItemServingOption::whereIn('food_item_id', $ids)
            ->orderBy('sort_order')->orderBy('id')->get()->groupBy('food_item_id');
        $images = FileLink::with('file')->where('object_type', 'food_image')->whereIn('object_id', $ids)
            ->orderByRaw("CASE WHEN type = 'M' THEN 0 ELSE 1 END")
            ->orderBy('position')->orderBy('id')->get()
            ->filter(fn($link) => $link->file && $link->file->is_image)->groupBy('object_id');
        $fileService = $images->isNotEmpty() ? app(FileService::class) : null;

        foreach ($records as $row) {
            $file = $images->get($row->id)?->first()?->file;
            $this->details[$row->id] = [
                'categories' => $categories->get($row->id, collect())->pluck('name')->unique()->implode(', '),
                'labels' => collect($itemLabelIds->get($row->id, []))->map(fn ($id) => $labels->get($id))->filter()->values(),
                'options' => $options->get($row->id, collect()),
                'imageUrl' => $file ? $fileService->getStoredFileUrl($file) : null,
            ];
        }

        return parent::formatRecords($records);
    }

    public function prepareColumns()
    {
        $this->addColumn(['index' => 'id', 'label' => 'ID', 'type' => 'integer', 'visibility' => false]);
        $this->addColumn([
            'index' => 'food_image',
            'label' => 'Image',
            'type' => 'string',
            'filterable' => true, 'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'With image', 'value' => 'with_image'],
                ['label' => 'Without image', 'value' => 'without_image'],
            ],
            'closure' => fn($row) => view('foodmanagements::food_items.grid.image', [
                'imageUrl' => $this->details[$row->id]['imageUrl'] ?? null,
                'name' => $row->name,
            ])->render(),
        ]);
        $this->addColumn([
            'index' => 'name',
            'label' => 'Food Item',
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'closure' => fn($row) => e($row->name),
        ]);
        $this->addColumn([
            'index' => 'category',
            'label' => 'Category',
            'type' => 'string',
            'filterable' => true, 'filterable_type' => 'dropdown',
            'filterable_options' => fn () => $this->filterOptions('category'),
            'closure' => fn($row) => e(($this->details[$row->id]['categories'] ?? '') ?: '—'),
        ]);
        $this->addColumn([
            'index' => 'dietary_label',
            'label' => 'Dietary Labels',
            'type' => 'string',
            'filterable' => true, 'filterable_type' => 'dropdown',
            'filterable_options' => fn () => $this->filterOptions('dietary_label'),
            'closure' => fn($row) => view('foodmanagements::food_items.grid.dietary-labels', [
                'labels' => $this->details[$row->id]['labels'] ?? collect(),
            ])->render(),
        ]);
        $this->addColumn([
            'index' => 'serving_options',
            'label' => 'Serving Options',
            'type' => 'string',
            'closure' => fn($row) => view('foodmanagements::food_items.grid.serving-options', [
                'options' => $this->details[$row->id]['options'] ?? collect(),
            ])->render(),
        ]);
        $this->addColumn([
            'index' => 'serve_type', 'label' => 'Serve Type', 'type' => 'string', 'visibility' => false,
            'filterable' => true, 'filterable_type' => 'dropdown',
            'filterable_options' => collect(app(\Modules\FoodManagements\Services\ServingOptionConfiguration::class)->all()['serve_types'])
                ->map(fn ($type, $value) => ['label' => $type['label'], 'value' => $value])->values()->all(),
        ]);
        $this->addColumn([
            'index' => 'sale_price',
            'label' => 'Price Before Tax',
            'type' => 'decimal',
            'filterable' => true,
            'filterable_help' => 'Use 20, 10-50, or >=20. Matches an available option after discount, before tax.',
            'closure' => fn($row) => view('foodmanagements::food_items.grid.price', [
                'options' => $this->details[$row->id]['options'] ?? collect(),
                'legacyPrice' => $row->sale_price,
            ])->render(),
        ]);
        $this->addColumn([
            'index' => 'tax_percentage',
            'label' => 'Tax',
            'type' => 'decimal',
            'sortable' => true,
            'filterable' => true,
            'filterable_help' => 'Enter a percentage such as 0 or 8.25, a range such as 5-10, or >=5.',
            'closure' => fn($row) => number_format((float) $row->tax_percentage, 2) . '%',
        ]);
        foreach ([
            'is_available' => ['Availability', 'Available', 'Unavailable'],
            'is_active' => ['Status', 'Active', 'Inactive'],
        ] as $index => [$label, $yes, $no]) {
            $this->addColumn([
                'index' => $index,
                'label' => $label,
                'type' => 'boolean',
                'sortable' => true,
                'filterable' => true,
                'filterable_type' => 'dropdown',
                'filterable_options' => [['label' => $yes, 'value' => 1], ['label' => $no, 'value' => 0]],
                'closure' => fn($row) => '<span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs '
                    . ($row->$index ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600') . '">'
                    . ($row->$index ? $yes : $no) . '</span>',
            ]);
        }
        $this->addColumn([
            'index' => 'created_at',
            'label' => 'Created At',
            'type' => 'date',
            'sortable' => true,
            'visibility' => false,
            'filterable' => true, 'filterable_type' => 'date_range',
        ]);
        $this->addFilter('name', 'food_items.name');
    }

    /**
     * Row actions.
     */
    public function prepareActions()
    {
        // Get restaurant ID from route if available
        $restaurantId = $this->restaurantId();

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.show')) {
            $this->addAction([
                'icon' => 'visibility',
                'title' => 'View Details',
                'method' => 'GET',
                'url' => fn($row) => route('admin.foodmanagements.food-items.show', [
                    'food_item' => $row->id,
                    ...($restaurantId ? ['restaurant_id' => $restaurantId, 'from_restaurant' => 1] : []),
                ]),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.edit')) {
            $this->addAction([
                'icon' => 'edit',
                'title' => 'Edit',
                'method' => 'GET',
                'url' => fn($row) => $restaurantId
                    ? route('admin.foodmanagements.food-items.edit', [
                        'food_item' => $row->id,
                        'restaurant_id' => $restaurantId,
                        'from_restaurant' => 1
                    ])
                    : route('admin.foodmanagements.food-items.edit', ['food_item' => $row->id]),
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.destroy')) {
            $this->addAction([
                'icon' => 'delete',
                'title' => 'Delete',
                'method' => 'DELETE',
                'url' => fn($row) =>
                    route('admin.foodmanagements.food-items.destroy', $row->id),
            ]);
        }
    }

    /**
     * Bulk actions.
     */
    public function prepareMassActions()
    {
        $restaurantId = $this->restaurantId();
        if (bouncer()->hasPermission('admin.foodmanagements.food-items.bulk-delete')) {
            $this->addMassAction([
                'icon' => 'delete',
                'title' => 'Bulk Delete',
                'method' => 'POST',
                'action' => 'bg-red-600 text-white',
                'url' => 'admin.foodmanagements.food-items.bulk-delete',
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.toggle-status')) {
            $this->addMassAction([
                'icon' => 'sync',
                'title' => 'Change Status',
                'method' => 'POST',
                'url' => 'admin.foodmanagements.food-items.toggle-status',
                'action' => 'bg-gray-600 text-white',
                'options' => [
                    ['label' => 'Active', 'value' => 1],
                    ['label' => 'Inactive', 'value' => 0],
                ],
            ]);
        }

        if (bouncer()->hasPermission('admin.foodmanagements.food-items.create')) {
            $this->addMassAction([
                'icon' => 'add',
                'title' => 'Create Food Item',
                'method' => 'GET',
                'is_popup' => false,
                'action' => 'bg-blue-600 text-white',
                'url' => $restaurantId
                    ? route('admin.foodmanagements.food-items.create', ['restaurant_id' => $restaurantId, 'from_restaurant' => 1])
                    : 'admin.foodmanagements.food-items.create',
            ]);
        }
    }
}
