<?php

namespace Modules\Orders\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Orders\DataView\OrderCommissionGrid;
use Modules\Orders\Models\OrderCommission;
use Modules\Orders\Services\OrderCommissionAccess;
use Modules\Orders\Services\OrderCommissionService;
use Modules\Payments\Models\Refund;

class OrderCommissionController extends Controller
{
    public function __construct(private OrderCommissionAccess $access, private OrderCommissionService $commissions) {}

    public function index(Request $request)
    {
        $this->access->actor($request);
        $grid = fn_datagrid(OrderCommissionGrid::class);
        $lists = $grid->process();
        if (! is_array($lists)) {
            return $lists;
        }

        $summary = $grid->summary();

        return response()->view('orders::commissions.index', compact('lists', 'summary'))->header('Cache-Control', 'no-store, private');
    }

    public function show(Request $request, int $id)
    {
        $actor = $this->access->actor($request);
        $commission = $this->access->query($actor)->with(['order', 'restaurant', 'processor', 'histories.actor'])->findOrFail($id);
        $canUpdate = $this->access->canUpdate($actor) && ! $commission->isStatusLocked();
        $statuses = $canUpdate ? $commission->availableStatuses() : [];
        $refundIds = $commission->histories->map(function ($history) {
            preg_match('/^Refund #(\d+)\.\s*/', $history->notes ?? '', $matches);

            return $matches[1] ?? null;
        })->filter()->unique();
        $refunds = Refund::where('order_id', $commission->order_id)->whereIn('id', $refundIds)
            ->get(['id', 'refund_number'])->keyBy('id');
        $historyEntries = $commission->histories->reverse()->map(function ($history) use ($refunds) {
            preg_match('/^Refund #(\d+)\.\s*/', $history->notes ?? '', $matches);

            return [
                'history' => $history,
                'refund' => isset($matches[1]) ? $refunds->get($matches[1]) : null,
                'refundUpdate' => isset($matches[1]),
                'notes' => preg_replace('/^Refund #\d+\.\s*/', '', $history->notes ?? ''),
            ];
        });

        return response()->view('orders::commissions.show', compact('commission', 'canUpdate', 'statuses', 'historyEntries'))->header('Cache-Control', 'no-store, private');
    }

    public function update(Request $request, int $id)
    {
        $actor = $this->access->actor($request, true);
        $commission = $this->access->query($actor)->findOrFail($id);
        $data = $request->validate(['status' => ['required', Rule::in($commission->availableStatuses())],
            'admin_notes' => ['nullable', 'string', 'max:5000']]);
        $this->commissions->updateStatus($commission, $data, $actor);

        return redirect()->route('admin.order-commissions.show', $id)->with('success', 'Commission status updated.');
    }
}
