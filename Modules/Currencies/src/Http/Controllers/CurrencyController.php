<?php

namespace Modules\Currencies\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Currencies\Services\CurrencyService;
use Modules\Currencies\DataView\CurrencyView;
use Illuminate\Validation\Rule;
use Throwable;

class CurrencyController extends Controller
{
    protected $currencyService;
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(CurrencyView::class)->process();
        return view('currencies::currencies.index', compact('lists'));
    }

    public function create()
    {
        return view('currencies::currencies.form');
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:10',

                Rule::unique('currencies')->where(function ($query) use ($request) {
                    return $query->where('code', $request->code)
                        ->where('symbol', $request->symbol);
                })
            ],
            'name' => 'required|string|max:80',
            'symbol' => 'string|nullable|max:10',
            'decimal' => 'required|integer|min:0|max:5',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $user = $this->currencyService->create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'currency created successfully!',
                'redirect_url' => route('settings.currencies.index'),
                'data' => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again. ' . $e
            ]);
        }
    }

    public function show($id)
    {
        $user = $this->currencyService->find($id);
        if (!$user) {
            return redirect()->route('settings.currencies.index')->with('error', 'User not found.');
        }
        return view('currencies::currencies.form', compact('user'));
    }

    public function edit($id)
    {
        $currency = $this->currencyService->find($id);
        return view('currencies::currencies.form', compact('currency'));
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('currencies')
                    ->ignore($id)
                    ->where(function ($query) use ($request) {
                        return $query->where('code', $request->code)
                            ->where('symbol', $request->symbol);
                    }),
            ],
            'name' => 'required|string|max:80',
            'symbol' => 'required|string|max:10',
            'decimal' => 'required|integer|min:0|max:5',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $currency = $this->currencyService->update($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Currency updated successfully!',
                'redirect_url' => route('settings.currencies.index'),
                'data' => $currency
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.' . $e
            ]);
        }
    }
    public function destroy($id)
    {
        try {
            $this->currencyService->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'Currency deleted Successfully',
                'redirect_url' => route('settings.currencies.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:currencies,id',
        ]);
        try {
            $deletedCount = $this->currencyService->deleteMultiple($request->ids);
            return redirect()->route('settings.currencies.index')->with('success', 'Bulk Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->route('settings.currencies.index')->with('error', 'Something went wrong. Please try again.');
        }
    }
}
