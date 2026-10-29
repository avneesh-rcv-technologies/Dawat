<?php

namespace Modules\FoodManagements\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\FoodManagements\DataView\FoodFeatureGrid;
use Modules\FoodManagements\Models\FoodCategory;
use Modules\FoodManagements\Models\FoodFeature;
use Modules\FoodManagements\Services\FoodFeatureService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class FoodFeaturesController extends Controller
{
    public function __construct(
        protected FoodFeatureService $service
    ) {}

    public function index()
    {
        $lists = fn_datagrid(FoodFeatureGrid::class)->process();

        return view('foodmanagements::food_features.index', compact('lists'));
    }

    public function create()
    {
        return view('foodmanagements::food_features.form', $this->formLookups());
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Food feature created successfully!',
                'redirect_url' => route('admin.foodmanagements.features.index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function edit(FoodFeature $feature)
    {
        $feature->load(['description', 'categories', 'variants.description', 'parent.description']);

        return view('foodmanagements::food_features.form', array_merge(
            $this->formLookups(),
            ['feature' => $feature]
        ));
    }

    public function update(Request $request, FoodFeature $feature)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules($feature->feature_id));

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->service->update($feature, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Food feature updated successfully!',
                'redirect_url' => route('admin.foodmanagements.features.index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function destroy(FoodFeature $feature)
    {
        try {
            $this->service->delete($feature);

            return response()->json([
                'success' => true,
                'message' => 'Food feature deleted successfully!',
                'redirect_url' => route('admin.foodmanagements.features.index'),
            ]);
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids'   => 'required|array|min:1',
                'ids.*' => 'exists:food_features,feature_id',
            ]);

            $this->service->bulkDelete($request->ids);

            if ($request->input('is_ajax')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Selected food features deleted successfully!',
                    'redirect_url' => route('admin.foodmanagements.features.index'),
                ]);
            }

            return redirect()->route('admin.foodmanagements.features.index')->with('success', 'Selected food features deleted successfully!');
        } catch (Throwable $e) {
            return ApiExceptionHandler::handle($e);
        }
    }

    private function rules(?int $featureId = null): array
    {
        return [
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'feature_code'       => 'nullable|string|max:32',
            'feature_type'       => 'nullable|string|max:1',
            'purpose'            => 'nullable|string|max:32',
            'feature_style'      => 'nullable|string|max:32',
            'filter_style'       => 'nullable|string|max:32',
            'parent_id'          => 'nullable|integer|min:0',
            'display_on_product' => 'nullable',
            'display_on_catalog' => 'nullable',
            'display_on_header'  => 'nullable',
            'status'             => 'nullable|string|max:1',
            'position'           => 'nullable|integer',
            'comparison'         => 'nullable',
            'category_ids'       => 'nullable|array',
            'category_ids.*'     => 'exists:food_categories,id',
            'variants'           => 'nullable|array',
            'variants.*.variant_id'   => 'nullable|integer',
            'variants.*.description'  => 'nullable|string|max:255',
            'variants.*.position'     => 'nullable|integer',
            'variants.*.status'       => 'nullable',
        ];
    }

    private function featurePurposes(): array
    {
        $purposes = [
            'find_products' => [
                'label' => 'Find products',
                'description' => 'Use this feature in product filtering and search.',
                'styles_map' => [
                    'text_checkbox' => ['feature_style' => 'text', 'feature_style_label' => 'Text', 'filter_style' => 'checkbox', 'filter_style_label' => 'Checkbox', 'feature_type' => 'S'],
                    'checkbox_checkbox' => ['feature_style' => 'checkbox', 'feature_style_label' => 'Checkbox', 'filter_style' => 'checkbox', 'filter_style_label' => 'Checkbox', 'feature_type' => 'C'],
                    'multiple_checkbox_checkbox' => ['feature_style' => 'multiple_checkbox', 'feature_style_label' => 'Multiple checkboxes', 'filter_style' => 'checkbox', 'filter_style_label' => 'Checkbox', 'feature_type' => 'M'],
                    'text_date' => ['feature_style' => 'text', 'feature_style_label' => 'Text', 'filter_style' => 'date', 'filter_style_label' => 'Date', 'feature_type' => 'D'],
                    'text_slider' => ['feature_style' => 'text', 'feature_style_label' => 'Text', 'filter_style' => 'slider', 'filter_style_label' => 'Slider', 'feature_type' => 'N'],
                    'simple_number_slider' => ['feature_style' => 'number', 'feature_style_label' => 'Number', 'filter_style' => 'slider', 'filter_style_label' => 'Slider', 'feature_type' => 'O'],
                    'text_color' => ['feature_style' => 'text', 'feature_style_label' => 'Text', 'filter_style' => 'color', 'filter_style_label' => 'Color', 'feature_type' => 'S'],
                ],
            ],
            'group_variation_catalog_item' => [
                'label' => 'Create product variants',
                'description' => 'Use this feature for variation options when generating product variants.',
                'styles_map' => [
                    'dropdown_checkbox' => ['feature_style' => 'dropdown', 'feature_style_label' => 'Dropdown', 'filter_style' => 'checkbox', 'filter_style_label' => 'Checkbox', 'feature_type' => 'S'],
                    'dropdown_checkbox_images' => ['feature_style' => 'dropdown_images', 'feature_style_label' => 'Images', 'filter_style' => 'checkbox', 'filter_style_label' => 'Checkbox', 'feature_type' => 'S'],
                    'dropdown_checkbox_labels' => ['feature_style' => 'dropdown_labels', 'feature_style_label' => 'Text labels', 'filter_style' => 'checkbox', 'filter_style_label' => 'Checkbox', 'feature_type' => 'S'],
                    'dropdown_slider' => ['feature_style' => 'dropdown', 'feature_style_label' => 'Dropdown', 'filter_style' => 'slider', 'filter_style_label' => 'Slider', 'feature_type' => 'N'],
                    'dropdown_slider_labels' => ['feature_style' => 'dropdown_labels', 'feature_style_label' => 'Dropdown Labels', 'filter_style' => 'slider', 'filter_style_label' => 'Slider', 'feature_type' => 'N'],
                    'dropdown_color' => ['feature_style' => 'dropdown', 'feature_style_label' => 'Dropdown', 'filter_style' => 'color', 'filter_style_label' => 'Color', 'feature_type' => 'S'],
                    'dropdown_color_images' => ['feature_style' => 'dropdown_images', 'feature_style_label' => 'Dropdown Images', 'filter_style' => 'color', 'filter_style_label' => 'Color', 'feature_type' => 'S'],
                    'dropdown_color_labels' => ['feature_style' => 'dropdown_labels', 'feature_style_label' => 'Dropdown Labels', 'filter_style' => 'color', 'filter_style_label' => 'Color', 'feature_type' => 'S'],
                ],
            ],
            'organize_catalog' => [
                'label' => 'Organize catalog',
                'description' => 'Use this feature to organize items similar to a brand.',
                'styles_map' => [
                    'default' => ['feature_style' => 'brand', 'feature_style_label' => 'Brand', 'filter_style' => 'checkbox', 'filter_style_label' => 'Checkbox', 'feature_type' => 'E'],
                ],
            ],
            'describe_product' => [
                'label' => 'Describe product',
                'description' => 'Use this feature to describe products without storefront filters.',
                'styles_map' => [
                    'default' => ['feature_style' => 'text', 'feature_style_label' => 'Text', 'filter_style' => null, 'filter_style_label' => null, 'feature_type' => 'T'],
                ],
            ],
        ];

        foreach ($purposes as &$purposeData) {
            $purposeData['types'] = [];
            foreach (($purposeData['styles_map'] ?? []) as $styleKey => $styleItem) {
                $type = (string) ($styleItem['feature_type'] ?? '');
                if ($type === '') continue;
                if (!isset($purposeData['types'][$type])) $purposeData['types'][$type] = [];
                $purposeData['types'][$type][$styleKey] = $styleItem;
            }
        }
        unset($purposeData);

        return $purposes;
    }

    private function formLookups(): array
    {
        return [
            'feature' => null,
            'featureTypes' => [
                'C' => 'Single checkbox',
                'M' => 'Multiple checkboxes',
                'S' => 'Select text',
                'N' => 'Select number',
                'E' => 'Brand / Extended',
                'T' => 'Text',
                'O' => 'Number',
                'D' => 'Date',
                'G' => 'Group',
            ],
            'featurePurposes' => $this->featurePurposes(),
            'defaultPurpose' => 'find_products',
            'statusOptions' => ['A' => 'Active', 'H' => 'Hidden', 'D' => 'Disabled'],
            'groups' => FoodFeature::with('description')->where('feature_type', 'G')->orderBy('position')->get(),
            'categories' => FoodCategory::orderBy('name')->get(),
        ];
    }
}
