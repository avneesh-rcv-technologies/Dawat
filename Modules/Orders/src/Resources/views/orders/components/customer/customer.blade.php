 <div class="mb-6 group">
     <div>
         <h3 class="text-sm font-medium text-gray-500 mb-2 flex items-center justify-between">
             <span>CUSTOMER DETAILS</span>
             @if($mode === 'create' || $mode === 'edit')
             @if(!empty($customer_details['id']))
             <x-modal
                 buttonText='<i class="fas fa-edit"></i>'
                 type='link'
                 modalTitle="Update Customer"
                 id="add_cart_update_customer"
                 ajaxUrl="{{route('api.cart.customer.profile', $customer_details['id'])}}"
                 buttonClass="text-primary-100 hover:text-amber-200 text-sm flex items-center gap-1"
                 modalSize="3xl" />
             @endif
             @endif
         </h3>
         <div id="customer_details" class="rounded-xl p-4 pl-2 space-y-1 bg-gray-50">
             @if(!empty($customer_details) && !empty($customer_details['id']))
             <p class="text-gray-800">
                 <span class="font-medium">{{ $customer_details['name'] ?? 'N/A' }}</span>
                 (Customer ID: #{{ $customer_details['id'] }})
             </p>
             <p class="text-gray-800">{{ $customer_details['email'] ?? 'N/A' }}</p>
             <p class="text-gray-800">{{ $customer_details['phone'] ?? 'N/A' }}</p>
             @else
             <div class="rounded-xl shadow-sm p-4 border divide-gray-100 rounded-lg bg-gray-50">
                 <p class="text-sm text-gray-600">No Customer Added</p>
             </div>
             @endif
             @if($mode === 'create' || $mode === 'edit')
             <x-modal
                 buttonText='Change Customer'
                 modalTitle="Change Customer"
                 id="add_cart_change_customer"
                 ajaxUrl="{{route('api.cart.customer.form', $order_id ?? 0)}}"
                 buttonClass="bg-primary-100 text-amber-100 hover:text-amber-200 p-1 rounded text-sm flex items-center gap-1"
                 modalSize="2xl" />
             @endif

         </div>
     </div>
 </div>