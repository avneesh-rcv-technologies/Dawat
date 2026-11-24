<?php

namespace Modules\Payments\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Payments\Models\Refund;
use Modules\Payments\Services\RefundAccess;
use Modules\Payments\Services\RefundService;

class RefundController extends Controller
{
    public function __construct(private RefundAccess $access, private RefundService $service) {}

    public function index(Request $request): JsonResponse
    {
        $actor = $this->access->actor($request);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Refund::STATUSES)],
            'order_id' => ['nullable', 'integer', 'min:1'],
            'order_number' => ['nullable', 'string', 'max:100'],
            'refund_number' => ['nullable', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $orderNumber = trim($filters['order_number'] ?? '');
        $refundNumber = trim($filters['refund_number'] ?? '');
        $refunds = $this->access->query($actor)->with(['order.commission', 'payment', 'transaction'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('refund_status', $status))
            ->when($filters['order_id'] ?? null, fn ($q, $id) => $q->where('order_id', $id))
            ->when($orderNumber !== '', fn ($q) => $q->whereHas('order', fn ($orders) => $orders
                ->where('order_number', 'like', '%'.addcslashes($orderNumber, '%_\\').'%')))
            ->when($refundNumber !== '', fn ($q) => $q->where('refund_number', 'like', '%'.addcslashes($refundNumber, '%_\\').'%'))
            ->latest('id')->paginate($filters['per_page'] ?? 20)->withQueryString();
        $refunds->through(fn (Refund $refund) => $refund->managementData());

        return response()->json(['success' => true, 'data' => $refunds])->header('Cache-Control', 'no-store, private');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $actor = $this->access->actor($request);
        $refund = $this->access->query($actor)->with(['order.commission', 'payment', 'transaction', 'histories'])->findOrFail($id);

        return $this->response($refund, 'Refund retrieved successfully.', $this->access->canUpdate($actor));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->save($request, $id, false);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        return $this->save($request, $id, true);
    }

    private function save(Request $request, int $id, bool $statusRequired): JsonResponse
    {
        $actor = $this->access->actor($request, true);
        $refund = $this->access->query($actor)->findOrFail($id);
        $data = $request->validate([
            'refund_status' => $statusRequired ? ['required', Rule::in(Refund::STATUSES)] : ['sometimes', 'required', Rule::in(Refund::STATUSES)],
            'refund_amount' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'stripe_refund_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'admin_notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);
        if ($data === []) {
            throw ValidationException::withMessages([
                'refund_status' => 'Provide at least one refund field to update.',
            ]);
        }
        $refund = $this->service->update($refund, $data, $actor);
        $refund->load(['order.commission', 'payment', 'transaction', 'histories']);

        return $this->response($refund, 'Refund updated successfully.', true);
    }

    private function response(Refund $refund, string $message, bool $canUpdate): JsonResponse
    {
        return response()->json([
            'success' => true, 'message' => $message, 'data' => $refund->managementData(),
            'meta' => [
                'can_update' => $canUpdate && $refund->refund_status !== 'succeeded',
                'allowed_statuses' => $canUpdate && $refund->refund_status !== 'succeeded'
                    ? $this->service->availableStatuses($refund) : [],
            ],
        ])->header('Cache-Control', 'no-store, private');
    }
}
