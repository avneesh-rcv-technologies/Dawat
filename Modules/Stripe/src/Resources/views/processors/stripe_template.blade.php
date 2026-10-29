@php
    $isCheckoutMode = (bool) ($checkoutFormMode ?? false);

    $buttonLabel = trim((string) ($submitLabel ?? 'Place Order'));
    if ($buttonLabel === '') {
        $buttonLabel = 'Place Order';
    }

    $methodName = trim((string) (
        ($paymentConfiguration->merchant_name ?? null)
        ?? ($paymentMethod->name ?? null)
        ?? 'Stripe'
    ));
    if ($methodName === '') {
        $methodName = 'Stripe';
    }
@endphp

@if($isCheckoutMode)
    <div class="space-y-2" data-payment-code="{{ (string) ($paymentMethod->code ?? 'stripe') }}">
        <button
            type="submit"
            class="w-full rounded-lg bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-black disabled:opacity-60 disabled:cursor-not-allowed"
        >
            {{ $buttonLabel }}
        </button>
        <p class="text-xs text-gray-500">
            Payment method: {{ $methodName }}
        </p>
    </div>
@else
    <div id="stripe-form" data-init="initializeStripePayment">
        <form id="payment-form" method="post">
            <button
                id="card-button"
                type="button"
                class="px-5 py-2.5 text-sm font-medium text-white gradient-bg rounded-lg hover:opacity-90 transition duration-200 flex items-center shadow-md hover:shadow-lg"
            >
                Pay
            </button>
            <div id="card-errors" class="text-red-600 text-sm mt-2"></div>
        </form>
    </div>

    <script>
      async function initializeStripePayment() {
        const cardButton = document.getElementById("card-button");
        if (!cardButton) return;

        cardButton.addEventListener("click", async () => {
          try {
            const select = document.querySelector("#payment_method");
            const payment = select?.value;

            ceAjax("POST", "{{ route('api.checkout.payment.charge', $order['order_id'] ?? 0) }}", {
              loader: true,
              data: { payment },
              callback: function (data) {
                let errorMessage = data.message || "";

                if (!errorMessage && data.errors) {
                  if (Array.isArray(data.errors)) {
                    errorMessage = data.errors.join(", ");
                  } else if (typeof data.errors === "object") {
                    errorMessage = Object.values(data.errors).flat().join(", ");
                  } else {
                    errorMessage = data.errors.toString();
                  }
                  window.notify("Payment failed: " + errorMessage, "error");
                }

                if (data.success && data.message) {
                  window.notify(data.message);
                }

                if (data.redirect_url) {
                  window.location.href = data.redirect_url;
                }
              },
              errorCallback: function () {
                window.notify("Something went wrong", "error");
              },
            });
          } catch (err) {
            const errorBox = document.getElementById("card-errors");
            if (errorBox) {
              errorBox.innerText = "Payment failed. Try again.";
            }
          }
        });
      }

      initializeStripePayment();
    </script>
@endif
