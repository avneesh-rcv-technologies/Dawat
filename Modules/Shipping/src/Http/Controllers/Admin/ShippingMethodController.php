<?php

namespace Modules\Shipping\Http\Controllers\Admin;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Shipping\Models\ShippingMethod;
use Modules\Filemanager\Services\FileService;
use Illuminate\Validation\ValidationException;
use Modules\Shipping\DataView\ShippingMethodView;
use Modules\Shipping\Models\ShippingConfiguration;

class ShippingMethodController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(ShippingMethodView::class)->process();
        return view('shipping::admin.index', compact('lists'));
    }

    public function create()
    {
        $processors = ShippingMethod::get();
        return view('shipping::admin.create', compact('processors'));
    }

    public function edit(Request $request, $shipping_id)
    {
        try {
            $processors = ShippingMethod::get();
            $shipping = ShippingConfiguration::findorfail($shipping_id);
            return view('shipping::admin.update', compact('processors', 'shipping'));
        } catch (Exception $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'processor' => 'required|string|exists:shipping_methods,code',
            'shipping_data.is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        try {
            // Find the shipping method by processor code (e.g., 'takeaway', 'delivery')
            $shippingMethod = ShippingMethod::where('code', $validated['processor'])->firstOrFail();

            // Create the configuration
            $shipping = ShippingConfiguration::create([
                'shipping_method_id' => $shippingMethod->id,
                'configuration' => Arr::except($request->shipping_data ?? [], ['is_active']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shipping method configured successfully!',
                'redirect_url' => route('admin.shipping_methods.index')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'processor' => 'required|string|exists:shipping_methods,code',
            'shipping_data.is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        try {
            // Find the shipping method
            $shippingMethod = ShippingMethod::where('code', $validated['processor'])->firstOrFail();

            // Find the existing configuration
            $config = ShippingConfiguration::findOrFail($id);

            // Update the configuration
            $config->update([
                'shipping_method_id' => $shippingMethod->id,
                'configuration' => Arr::except($request->shipping_data ?? [], ['is_active']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shipping configuration updated successfully!',
                'redirect_url' => route('admin.shipping_methods.index')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e->getMessage()
            ]);
        }
    }

    public function loadConfigForm(Request $request, $processorCode)
    {
        try {
            $method = ShippingMethod::where('code', $processorCode)->first();

            if (!empty($method)) {
                $getConfiguration = ShippingConfiguration::where('shipping_method_id', $method->id)->first();

                $view = $method->template;

                if (!view()->exists($view)) {
                    return response()->json([
                        'errors' => "View not exists {$processorCode}",
                    ]);
                }

                $configuration = [];
                if ($getConfiguration && $getConfiguration->configuration) {
                    $configuration = $getConfiguration->configuration;
                }

                $html = view($view, ['configuration' => $configuration])->render();

                return response()->json([
                    'success' => true,
                    'shipping_configuration' => $html,
                ]);
            } else {
                return response()->json([
                    'errors' => "No configuration view available for {$processorCode}",
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'errors' => "View not exists {$e->getMessage()}",
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            ShippingConfiguration::destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Shipping method deleted',
                'redirect_url' => route('admin.shipping_methods.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }
}