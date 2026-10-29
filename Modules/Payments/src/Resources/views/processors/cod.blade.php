<div id="code-form" data-init="initializePayment">
  <form id="payment-form" method="post">
    <!-- Pay button -->
    <button
      id="card-button"
      type="button"
      class="px-5 py-2.5 text-sm font-medium text-white gradient-bg rounded-lg hover:opacity-90 transition duration-200 flex items-center shadow-md hover:shadow-lg">
      Place Order
    </button>
  </form>
</div>

<script>
  async function initializePayment() {
  const formEl = document.querySelector("#payment-form");
  if (!formEl) return; // No form found

  const cardButton = document.getElementById("card-button");

  cardButton?.addEventListener("click", async () => {
    try {

        const select = document.querySelector("#payment_method");
        const payment = select?.value;
     
        ceAjax("POST", "{{ route('api.cart.payment.charge', $order['order_id']) }}", {
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
      console.error(err);
    }
  });
}

  initializePayment();
</script>