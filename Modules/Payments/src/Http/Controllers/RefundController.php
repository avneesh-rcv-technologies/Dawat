<?php

namespace Modules\Payments\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Payments\DataView\RefundGrid;
use Modules\Payments\Models\Refund;
use Modules\Payments\Services\RefundAccess;
use Modules\Payments\Services\RefundService;

class RefundController extends Controller
{
    public function __construct(private RefundAccess $access, private RefundService $service) {}

    public function index(Request $request)
    {
        $actor = $this->access->actor($request);
        $canUpdate = $this->access->canUpdate($actor);
        $grid = fn_datagrid(RefundGrid::class);
        $lists = $grid->process();
        if (! is_array($lists)) {
            return $lists;
        }
        $summary = $grid->summary();

        return response()->view('payments::refunds.index', compact('lists', 'summary', 'canUpdate'))->header('Cache-Control', 'no-store, private');
    }

    public function show(Request $request, int $id)
    {
        $actor = $this->access->actor($request);
        $refund = $this->access->query($actor)->with(['order.vendor', 'order.commission', 'payment.method.method', 'transaction', 'processor', 'histories.actor'])->findOrFail($id);
        $orderStatusNames = \Modules\Orders\Models\Status::where('type_code', 'O')->pluck('name', 'status_code');
        $canUpdate = $this->access->canUpdate($actor);
        $statuses = $canUpdate ? $this->service->availableStatuses($refund) : [];
        $amountRange = $this->service->amountRange($refund);
        $commission = $refund->order?->commission;
        $commissionPreview = $this->service->settlementPreview($refund);

        return response()->view('payments::refunds.show', compact('refund', 'canUpdate', 'statuses', 'orderStatusNames', 'amountRange', 'commission', 'commissionPreview'))->header('Cache-Control', 'no-store, private');
    }

    public function update(Request $request, int $id)
    {
        $actor = $this->access->actor($request, true);
        $refund = $this->access->query($actor)->findOrFail($id);
        $data = $request->validate([
            'refund_amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'refund_status' => ['required', Rule::in(Refund::STATUSES)],
            'stripe_refund_id' => ['nullable', 'string', 'max:255'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $refund = $this->service->update($refund, $data, $actor);

        return redirect()->route('admin.refunds.show', $refund->id)->with('success', 'Refund record updated.');
    }
}
