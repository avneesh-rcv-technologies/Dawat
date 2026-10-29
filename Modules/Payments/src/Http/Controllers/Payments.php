<?php

namespace Modules\Payments\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payments\Models\Payments as PaymentModel;
use Modules\Filemanager\Services\FileService;
use Modules\Payments\DataView\Payments as DataViewPayments;


class Payments extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request)
    {
        $lists = fn_datagrid(DataViewPayments::class)->process();
        return view('payments::payments.index', compact('lists'));
    }

    public function show(PaymentModel $payment)
    {
        $payment->load(['transactions', 'order']);

        return redirect()->route('admin.payments.index');
    }
}
