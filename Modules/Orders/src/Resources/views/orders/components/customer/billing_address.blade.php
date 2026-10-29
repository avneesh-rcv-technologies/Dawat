 <div class="mb-6 group border-t border-gray-100 pt-6">
     <div>
         <h3 class="text-sm font-medium text-gray-500 mb-2 flex items-center justify-between">
             <span>BILLING ADDRESS</span>
             @if($mode === 'create' || $mode === 'edit')
             @if(!empty($customer_details['id']))
             <x-modal
                 buttonText='<i class="fas fa-edit"></i>'
                 type='link'
                 modalTitle="Update Billing Address"
                 id="add_cart_update_billing"
                 ajaxUrl="{{route('api.cart.customer.profile', $customer_details['id'])}}"
                 buttonClass="text-primary-100 hover:text-amber-200 text-sm flex items-center gap-1"
                 modalSize="3xl" />
                 @endif
             @endif
         </h3>
         <div id="billing_address" class="rounded-xl p-4 pl-2 space-y-1 bg-gray-50">
             @if(!empty($billing_address))
             {!! \Modules\Customers\Helper\AddressHelper::format($billing_address, 'html') !!}
             @else
             <div class="rounded-xl shadow-sm p-4 border divide-gray-100 rounded-lg bg-gray-50">
                 <p class="text-sm text-gray-600">No Billing Address</p>
             </div>
             @endif
         </div>
     </div>
 </div>

 <div class="mb-6 group border-t border-gray-100 pt-6">
     <div>
         <h3 class="text-sm font-medium text-gray-500 mb-2 flex items-center justify-between">
             <span>SHIPPING ADDRESS</span>
             @if($mode === 'create' || $mode === 'edit')
             @if(!empty($customer_details['id']))
             <x-modal
                 buttonText='<i class="fas fa-edit"></i>'
                 type='link'
                 modalTitle="Update Billing Address"
                 id="add_cart_update_billing"
                 ajaxUrl="{{route('api.cart.customer.profile', $customer_details['id'])}}"
                 buttonClass="text-primary-100 hover:text-amber-200 text-sm flex items-center gap-1"
                 modalSize="3xl" />
                 @endif
             @endif
         </h3>
         <div id="shipping_address" class="rounded-xl p-4 pl-2 space-y-1 bg-gray-50">
             @if(!empty($shipping_address))
             {!! \Modules\Customers\Helper\AddressHelper::format($shipping_address, 'html') !!}
             @else
             <div class="rounded-xl shadow-sm p-4 border divide-gray-100 rounded-lg bg-gray-50">
                 <p class="text-sm text-gray-600">No Shipping Address</p>
             </div>
             @endif
         </div>
     </div>
 </div>