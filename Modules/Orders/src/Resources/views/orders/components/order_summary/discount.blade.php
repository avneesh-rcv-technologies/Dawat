
<!-- Add Discount Section -->
<div class="mt-6 pt-4 border-t border-gray-100">
    <div class="flex justify-between items-center mb-2">
        <h3 class="text-sm font-medium text-gray-700">DISCOUNTS</h3>
        @if($mode === 'create' || $mode === 'edit')
        <x-modal
            buttonText='<i class="fas fa-plus"></i> Add Discount'
            type='link'
            modalTitle="Add Discount"
            id="add_cart_discount"
            ajaxUrl="{{route('api.cart.discount.view', $order_id ?? 0)}}"
            buttonClass="text-primary-100 hover:text-primary-200 text-sm flex items-center gap-1"
            modalSize="2xl" />
        @endif
    </div>
    <div id="discount">
        @if(!empty($order_summary) && $order_summary['discount'] > 0)
        <div class="bg-primary-100 p-3 mb-2 rounded-lg flex justify-between items-center">
            <div>
                <p class="text-sm font-medium text-amber-100">DISCOUNT</p>
                <p class="text-xs text-amber-100">Applied Coupon Code: ({{$coupon_code}})</p>
            </div>
            <a href="javascript:;" id="remove_discount" class="text-amber-100 hover:text-amber-200">
                <i class="fas fa-times"></i>
            </a>
        </div>
        @endif
    </div>
</div>