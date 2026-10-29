@extends('backend::layouts.app')

@section('title')
    @if($mode === 'create')
        Create Order
    @elseif($mode === 'edit')
        Edit Order #{{ $order_number ?? '' }}
    @else
        Order Detail #{{ $order_number ?? '' }}
    @endif
@endsection

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        @php
            $redirect_route = route('admin.orders.index');
            if ($mode === 'create') {
                $redirect_route = route('admin.orders.index');
            } elseif ($mode === 'edit') {
                $order_title = 'Order #' . $order_number;
                $redirect_route = route('admin.orders.show', $order_id ?? 0);
            } else {
                $order_title = 'Order #' . $order_number . ' / Total: ' . fn_convert_currency($total ?? 0, $currency);
            }

        @endphp

        {{-- @include('admin::components.common.back-button', [
            'route' => $redirect_route,
            'name' => $mode === 'create' ? 'New Order' : $order_title
        ]) --}}


        {{-- @if($mode == 'view' && !empty($order_id))
            @include('dataview::components.dataView.components.actions-dropdown', [
                'icon' => 'keyboard_arrow_down',
                'title' => 'Actions',
                'class' => 'btn-primary justify-center items-center',
                'actions' => [
                    [
                        'title' => 'Print Order',
                        'icon' => '',
                        'method' => '',
                        'url' => ''
                    ],
                    [
                        'title' => 'Generate Invoice',
                        'icon' => '',
                        'method' => '',
                        'url' => ''
                    ],
                    [
                        'title' => 'Edit Order',
                        'icon' => '',
                        'method' => 'GET',
                        'url' => route('admin.orders.edit', $order_id ?? 0)
                    ],
                    [
                        'title' => 'Cancel Order',
                        'icon' => '',
                        'method' => '',
                        'url' => ''
                    ]
                ],
                "id" => $order_id ?? ''
            ])
        @else
            <x-button type="submit"
                class="primary"
                label="Save"
                icon=''
                id="update_order"
                name="button" />
        @endif --}}
    </div>

    <!-- Main Content Grid -->
    <div class="" id="order_datas">
        @include('orders::orders.components.order_detail')
    </div>

@endsection
@section('scripts')
    <script>
        @if($mode === 'create' || $mode === 'edit')
            document.addEventListener('DOMContentLoaded', function() {

                document.querySelectorAll('#update_order').forEach(button => {
                    if (button.dataset.bound) return;
                    button.dataset.bound = true;

                    button.addEventListener('click', () => {
                        mutations.save();
                    });
                });

                function getOrderContainer() {
                    return document.getElementById('order_datas');
                }

                // ------------------------------
                // Mutations for order operations
                // ------------------------------
                const mutations = {
                    updateItemQuantity(itemId, quantity) {
                        const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                        const originalQuantity = input?.getAttribute('data-original-quantity') || 1;

                        if (typeof ceAjax !== 'function') return console.error('ceAjax is not defined');

                        // For order management, we need to update order items directly
                        ceAjax('POST', '{{ route("admin.orders.update", $order_id ?? 0) }}', {
                            loader: true,
                            data: {
                                action: 'update_item_quantity',
                                item_id: itemId,
                                quantity: quantity,
                                _token: '{{ csrf_token() }}'
                            },
                            callback: function (response) {
                                if (response.success) {
                                    loadOrder();
                                    showToast('Quantity updated successfully', 'success');
                                } else {
                                    if (input) input.value = originalQuantity;
                                    showToast(response.message || 'Error updating quantity', 'error');
                                }
                            },
                            errorCallback: function() {
                                if (input) input.value = originalQuantity;
                                showToast('Error updating quantity', 'error');
                            }
                        });
                    },

                    removeItem(itemId, foodItemId, itemName) {
                        if (!confirm(`Are you sure you want to remove "${itemName}" from this order?`)) return;

                        if (typeof ceAjax !== 'function') return console.error('ceAjax is not defined');

                        ceAjax('POST', '{{ route("admin.orders.update", $order_id ?? 0) }}', {
                            loader: true,
                            data: {
                                action: 'remove_item',
                                item_id: itemId,
                                _token: '{{ csrf_token() }}'
                            },
                            callback: function(response) {
                                if (response.success) {
                                    showToast('Item removed successfully', 'success');
                                    loadOrder();
                                } else {
                                    showToast(response.message || 'Error removing item', 'error');
                                }
                            },
                            errorCallback: function() {
                                showToast('Error removing item', 'error');
                            }
                        });
                    },

                    removeCoupon() {
                        if (typeof ceAjax !== 'function') return console.error('ceAjax is not defined');

                        ceAjax('POST', '{{ route("admin.orders.update", $order_id ?? 0) }}', {
                            loader: true,
                            data: {
                                action: 'remove_coupon',
                                _token: '{{ csrf_token() }}'
                            },
                            callback: function(response) {
                                if (response.success) {
                                    showToast('Coupon removed successfully', 'success');
                                    loadOrder();
                                } else {
                                    showToast(response.message || 'Error removing coupon', 'error');
                                }
                            },
                            errorCallback: function() {
                                showToast('Error removing coupon', 'error');
                            }
                        });
                    },

                    payment_method_form(paymentCode, payment_id) {
                        if (typeof ceAjax !== 'function') return console.error('ceAjax is not defined');

                        ceAjax('GET', '{{ route("api.cart.payment.payment_form") }}', {
                            loader: true,
                            result_ids: 'payment-detail-action',
                            data: {
                                payment_id: payment_id,
                            },
                            callback: function(response) {

                            },
                errorCallback: function() {
                                showToast('Error removing coupon', 'error');
                            }
                        });
                    },

                    update_status(status) {
                        if (typeof ceAjax !== 'function') return console.error('ceAjax is not defined');

                        ceAjax('POST', '{{ route("api.cart.order.update_status", $order_id ?? 0) }}', {
                            loader: true,
                            result_ids: 'payment-detail-action',
                            data: {
                                status: status,
                            },
                             callback: function(response) {
                                 if (response.success) {
                                    showToast('Status updated successfully', 'success');
                                    loadOrder();
                                } else {
                                    showToast(response.message || 'Error status update', 'error');
                                }
                            },
                            errorCallback: function() {
                                showToast('Error removing coupon', 'error');
                            }
                        });
                    },

                      save() {
                         const form = document.querySelector('form#order_idForm');
                        if (form) {
                            form.submit();
                        } else {
                            console.warn('Form with ID #order_idForm not found.');
                        }
                    }
                };

                // ------------------------------
                // Load order
                // ------------------------------
                window.loadOrder = function() {
                    const container = getOrderContainer();
                    if (!container) return;

                    if (typeof ceAjax !== 'function') return console.error('ceAjax is not defined');

                    ceAjax('get', '{{ $mode === "create" ? route("admin.orders.create") : route("admin.orders.show", $order_id ?? 0) }}', {
                        loader: true,
                        data: {
                            tab: true,
                            order_id: '{{ $order_id ?? 0 }}'
                        },
                        result_ids: 'order_datas',
                        caching: false,
                        callback: function() {},
                        errorCallback: function() {
                            showToast('Unable to load order data', 'error', 'Error');
                        }
                    });
                }

                // ------------------------------
                // Attach events dynamically
                // ------------------------------
                function attachEvents(container) {
                    if (!container) return;

                    // Quantity increase/decrease
                    container.querySelectorAll('.quantity-increase, .quantity-decrease').forEach(button => {
                        if (button.dataset.bound) return;
                        button.dataset.bound = true;

                        button.addEventListener('click', () => {
                            const itemId = button.dataset.itemId;
                            const input = container.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                            let currentValue = parseInt(input.value) || 1;

                            if (button.classList.contains('quantity-increase')) currentValue++;
                            if (button.classList.contains('quantity-decrease') && currentValue > 1) currentValue--;

                            input.value = currentValue;
                            mutations.updateItemQuantity(itemId, currentValue);
                        });
                    });

                    // Quantity input
                    container.querySelectorAll('.quantity-input').forEach(input => {
                        if (input.dataset.bound) return;
                        input.dataset.bound = true;

                        input.addEventListener('change', () => {
                            let quantity = parseInt(input.value) || 1;
                            if (quantity < 1) quantity = 1;
                            input.value = quantity;
                            mutations.updateItemQuantity(input.dataset.itemId, quantity);
                        });

                        input.addEventListener('blur', () => {
                            let quantity = parseInt(input.value) || 1;
                            if (quantity < 1) quantity = 1;
                            input.value = quantity;
                            mutations.updateItemQuantity(input.dataset.itemId, quantity);
                        });
                     });

                    // Remove item
                    container.querySelectorAll('.remove-item').forEach(button => {
                        if (button.dataset.bound) return;
                        button.dataset.bound = true;

                        button.addEventListener('click', () => {
                            const itemId = button.dataset.itemId;
                            const foodItemId = button.dataset.foodItemId;
                            const itemName = button.closest('tr')?.querySelector('a')?.textContent || 'Item';
                            mutations.removeItem(itemId, foodItemId, itemName);
                        });
                     });

                    // Remove discount
                    container.querySelectorAll('#remove_discount').forEach(button => {
                        if (button.dataset.bound) return;
                        button.dataset.bound = true;
                        button.addEventListener('click', mutations.removeCoupon);
                    });

                    // payment method
                    container.querySelectorAll('#payment_method').forEach(select => {
                        if (select.dataset.bound) return;
                        select.dataset.bound = true;

                        select.addEventListener('change', function() {
                            const selectedOption = this.options[this.selectedIndex];
                            const selectedValue = selectedOption.dataset.paymentCode;

                            mutations.payment_method_form(selectedValue, this.value);
                        });
                    });

                    container.querySelectorAll('#order_status').forEach(select => {
                        if (select.closest('#status-update-form')) return;

                        if (select.dataset.bound) return;
                        select.dataset.bound = true;

                        select.addEventListener('change', function() {
                            mutations.update_status(this.value);
                        });
                    });

                }

                // ------------------------------
                // MutationObserver to watch DOM changes
                // ------------------------------
                const observer = new MutationObserver(() => {
                    const container = getOrderContainer();
                    attachEvents(container);
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                attachEvents(getOrderContainer());
            });

        @endif
    </script>

    @stack('scripts')
@endsection
