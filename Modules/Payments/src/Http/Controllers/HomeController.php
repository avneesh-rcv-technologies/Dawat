<?php

namespace Modules\Payments\Http\Controllers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Payments\Models\PaymentMethod;
use Modules\Filemanager\Services\FileService;
use Illuminate\Validation\ValidationException;
use Modules\Payments\DataView\PaymentsMethodView;
use Modules\Payments\Models\PaymentConfiguration;

class HomeController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(PaymentsMethodView::class)->process();
        return view('payments::index', compact('lists'));
    }

    public function create()
    {
        $processors = PaymentMethod::get();
        return view('payments::create', compact('processors'));
    }

    public function edit(Request $request, $payment_id)
    {
        try {
            $processors = PaymentMethod::get();
            $payment = PaymentConfiguration::findorfail($payment_id);
            return view('payments::update', compact('processors', 'payment'));
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
            'amount' => 'required|numeric|min:1|max:100000', // adjust range
            'processor' => 'required|string|exists:payment_methods,code',
            'payment_data.is_active' => 'nullable|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();
        try {
            // Find the payment method by processor code (e.g., 'stripe')
            $paymentMethod = PaymentMethod::where('code', $validated['processor'])->firstOrFail();

            // Create the configuration
            $payment = PaymentConfiguration::create([
                'payment_method_id' => $paymentMethod->id,
                'merchant_name' => $validated['name'],
                'amount' => $validated['amount'],
                'is_active' => isset($validated['payment_data']['is_active']) && $validated['payment_data']['is_active'] == '1',
                'config' => json_encode(Arr::except($request->payment_data, ['is_active'])),
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $fileLink = $this->fileService->uploadFile(
                    $request->file('image'),
                    'payment',
                    $payment->id
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully!',
                'redirect_url' => route('admin.payment_methods.index')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'processor' => 'required|string|exists:payment_methods,code',
            'payment_data.is_active' => 'nullable|boolean',
            'amount' => 'required|numeric|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        $validated = $validator->validated();

        try {
            // Find the payment method
            $paymentMethod = PaymentMethod::where('code', $validated['processor'])->firstOrFail();

            // Find the existing configuration (assumes one config per processor/payment method)
            $config = PaymentConfiguration::where('payment_method_id', $paymentMethod->id)->first();

            if (!$config) {
                return response()->json([
                    'errors' => 'Configuration not found.'
                ]);
            }
            // Update the configuration
            $config->update([
                'merchant_name' => $validated['name'],
                'amount' => $validated['amount'],
                'is_active' => isset($validated['payment_data']['is_active']) && $validated['payment_data']['is_active'] == '1',
                'config' => json_encode(Arr::except($request->payment_data, ['is_active'])),
            ]);
            // Handle image upload
            if ($request->hasFile('image')) {
                $fileLink = $this->fileService->uploadFile(
                    $request->file('image'),
                    'payment',
                    $config->id
                );
            }
            return response()->json([
                'success' => true,
                'message' => 'Payment configuration updated successfully!',
                'redirect_url' => route('admin.payment_methods.index')
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


    public function loadConfigForm(Request $request)
    {
        try {
            $method = PaymentMethod::where('code', $request->processorCode)->first();

            if (!empty($method)) {

                $getConfiguration = PaymentConfiguration::where('payment_method_id', $method->id)->first();

                $view = $method->template;

                if (!view()->exists($view)) {
                    return response()->json([
                        'errors' => "View not exists {$request->processorCode}",
                    ]);
                }

                $html = view($view, ['payment_data' => !empty($getConfiguration && $getConfiguration->toArray()['config']) ? json_decode($getConfiguration->toArray()['config'], true) : []])->render();

                return response()->json([
                    'success' => true,
                    'payment_configuration' => $html,
                ]);

                return view($view)->render();
            } else {
                return response()->json([
                    'errors' => "No configuration view available for {$request->processorCode}",
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
            PaymentConfiguration::destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Payment deleted',
                'redirect_url' => route('admin.payment_methods.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }
}
