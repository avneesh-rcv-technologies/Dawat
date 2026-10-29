<template>
  <div class="checkout-container">
    <!-- Checkout Header -->
    <div class="checkout-header">
      <h1>Checkout</h1>
      <p class="subtitle">Complete your purchase</p>
    </div>

    <!-- Main Content -->
    <div class="checkout-content">
      <!-- Left Column: Checkout Form -->
      <div class="checkout-form">
        <!-- Step Indicator -->
        <div class="steps-indicator">
          <div 
            v-for="(step, index) in steps" 
            :key="index"
            class="step"
            :class="{ active: currentStep >= index, completed: currentStep > index }"
          >
            <div class="step-number">{{ index + 1 }}</div>
            <div class="step-label">{{ step }}</div>
          </div>
        </div>

        <!-- Shipping Address -->
        <div v-show="currentStep === 0" class="form-section">
          <h2>Shipping Address</h2>
          <form @submit.prevent="validateShippingAddress">
            <div class="form-grid">
              <div class="form-group">
                <label>First Name</label>
                <input v-model="form.shippingAddress.firstName" type="text" required>
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input v-model="form.shippingAddress.lastName" type="text" required>
              </div>
              <div class="form-group full-width">
                <label>Street Address</label>
                <input v-model="form.shippingAddress.street" type="text" required>
              </div>
              <div class="form-group">
                <label>City</label>
                <input v-model="form.shippingAddress.city" type="text" required>
              </div>
              <div class="form-group">
                <label>State/Province</label>
                <input v-model="form.shippingAddress.state" type="text" required>
              </div>
              <div class="form-group">
                <label>ZIP/Postal Code</label>
                <input v-model="form.shippingAddress.zipCode" type="text" required>
              </div>
              <div class="form-group full-width">
                <label>Country</label>
                <input v-model="form.shippingAddress.country" type="text" required>
              </div>
              <div class="form-group full-width">
                <label>Phone</label>
                <input v-model="form.shippingAddress.phone" type="tel" required>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                Continue to Shipping Method
              </button>
            </div>
          </form>
        </div>

        <!-- Shipping Method -->
        <div v-show="currentStep === 1" class="form-section">
          <h2>Shipping Method</h2>
          <div class="shipping-methods">
            <div 
              v-for="method in shippingMethods"
              :key="method.id"
              class="shipping-option"
              :class="{ selected: form.shippingMethod === method.id }"
              @click="form.shippingMethod = method.id"
            >
              <input 
                type="radio" 
                :value="method.id"
                v-model="form.shippingMethod"
              >
              <div class="method-details">
                <h3>{{ method.name }}</h3>
                <p class="description">{{ method.description }}</p>
                <p class="delivery-time">{{ method.deliveryDays }}</p>
              </div>
              <div class="method-price">
                {{ formatCurrency(method.cost) }}
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button @click="previousStep" class="btn btn-secondary">Back</button>
            <button @click="nextStep" class="btn btn-primary">
              Continue to Billing
            </button>
          </div>
        </div>

        <!-- Billing Address -->
        <div v-show="currentStep === 2" class="form-section">
          <h2>Billing Address</h2>
          
          <div class="form-group checkbox">
            <input 
              type="checkbox" 
              v-model="form.billingAddressSameAsShipping"
              @change="handleBillingAddressToggle"
            >
            <label>Same as shipping address</label>
          </div>

          <form v-show="!form.billingAddressSameAsShipping" @submit.prevent="validateBillingAddress">
            <div class="form-grid">
              <div class="form-group">
                <label>First Name</label>
                <input v-model="form.billingAddress.firstName" type="text">
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input v-model="form.billingAddress.lastName" type="text">
              </div>
              <div class="form-group full-width">
                <label>Street Address</label>
                <input v-model="form.billingAddress.street" type="text">
              </div>
              <div class="form-group">
                <label>City</label>
                <input v-model="form.billingAddress.city" type="text">
              </div>
              <div class="form-group">
                <label>State/Province</label>
                <input v-model="form.billingAddress.state" type="text">
              </div>
              <div class="form-group">
                <label>ZIP/Postal Code</label>
                <input v-model="form.billingAddress.zipCode" type="text">
              </div>
            </div>
          </form>

          <div class="form-actions">
            <button @click="previousStep" class="btn btn-secondary">Back</button>
            <button @click="nextStep" class="btn btn-primary">
              Continue to Payment
            </button>
          </div>
        </div>

        <!-- Payment Method -->
        <div v-show="currentStep === 3" class="form-section">
          <h2>Payment Method</h2>
          
          <div class="payment-methods">
            <div 
              v-for="method in paymentMethods"
              :key="method.code"
              class="payment-option"
              :class="{ selected: form.paymentMethod === method.code }"
              @click="form.paymentMethod = method.code"
            >
              <input 
                type="radio" 
                :value="method.code"
                v-model="form.paymentMethod"
              >
              <div class="method-icon">
                <i :class="getPaymentIcon(method.code)"></i>
              </div>
              <div class="method-info">
                <h3>{{ method.name }}</h3>
                <p>{{ method.description }}</p>
              </div>
            </div>
          </div>

          <!-- Coupon Code -->
          <div class="coupon-section">
            <h3>Have a coupon code?</h3>
            <div class="coupon-input-group">
              <input 
                v-model="couponCode"
                type="text" 
                placeholder="Enter coupon code"
              >
              <button @click="applyCoupon" class="btn btn-secondary">
                Apply
              </button>
            </div>
            <div v-if="couponApplied" class="coupon-success">
              ✓ Coupon applied successfully
            </div>
          </div>

          <div class="form-actions">
            <button @click="previousStep" class="btn btn-secondary">Back</button>
            <button @click="submitCheckout" :disabled="processing" class="btn btn-primary btn-large">
              <span v-if="!processing">Complete Order</span>
              <span v-else>Processing...</span>
            </button>
          </div>
        </div>

        <!-- Order Confirmation -->
        <div v-show="currentStep === 4" class="form-section success">
          <div class="success-icon">✓</div>
          <h2>Order Confirmed!</h2>
          <p>Thank you for your purchase.</p>
          
          <div class="order-details">
            <div class="detail-row">
              <span>Order Number:</span>
              <strong>{{ orderDetails.orderNumber }}</strong>
            </div>
            <div class="detail-row">
              <span>Total Amount:</span>
              <strong>{{ formatCurrency(orderDetails.total) }}</strong>
            </div>
          </div>

          <div class="form-actions">
            <a href="/" class="btn btn-primary">Back to Home</a>
            <a :href="`/orders/${orderDetails.orderId}`" class="btn btn-secondary">
              View Order
            </a>
          </div>
        </div>
      </div>

      <!-- Right Column: Order Summary -->
      <div class="order-summary">
        <div class="summary-header">
          <h2>Order Summary</h2>
        </div>

        <!-- Items -->
        <div class="summary-items">
          <div v-for="item in cartItems" :key="item.id" class="summary-item">
            <div class="item-info">
              <p class="item-name">{{ item.name }}</p>
              <p class="item-qty">Qty: {{ item.quantity }}</p>
            </div>
            <div class="item-price">
              {{ formatCurrency(item.price * item.quantity) }}
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="summary-divider"></div>

        <!-- Totals -->
        <div class="summary-totals">
          <div class="total-row">
            <span>Subtotal:</span>
            <span>{{ formatCurrency(totals.subtotal) }}</span>
          </div>
          
          <div v-if="totals.shipping && totals.shipping.amount > 0" class="total-row">
            <span>Shipping:</span>
            <span>{{ formatCurrency(totals.shipping.amount) }}</span>
          </div>

          <div v-if="totals.tax && totals.tax.amount > 0" class="total-row">
            <span>Tax:</span>
            <span>{{ formatCurrency(totals.tax.amount) }}</span>
          </div>

          <div v-if="totals.discounts && totals.discounts.amount > 0" class="total-row discount">
            <span>Discount:</span>
            <span>-{{ formatCurrency(totals.discounts.amount) }}</span>
          </div>

          <div class="total-row total">
            <span>Total:</span>
            <span>{{ formatCurrency(totals.total) }}</span>
          </div>
        </div>

        <!-- Security Badge -->
        <div class="security-badge">
          <i class="icon-lock"></i>
          <span>Secure checkout powered by Square</span>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="error-message">
      {{ error }}
      <button @click="error = null" class="close-btn">×</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Checkout',
  
  data() {
    return {
      currentStep: 0,
      steps: ['Shipping', 'Method', 'Billing', 'Payment', 'Confirmation'],
      
      form: {
        shippingAddress: {
          firstName: '',
          lastName: '',
          street: '',
          city: '',
          state: '',
          zipCode: '',
          country: '',
          phone: '',
        },
        billingAddress: {
          firstName: '',
          lastName: '',
          street: '',
          city: '',
          state: '',
          zipCode: '',
          country: '',
          phone: '',
        },
        billingAddressSameAsShipping: true,
        shippingMethod: '',
        paymentMethod: '',
      },

      cartItems: [],
      shippingMethods: [
        {
          id: 'standard',
          name: 'Standard Shipping',
          description: 'Delivery in 5-7 business days',
          deliveryDays: '5-7 days',
          cost: 10.00,
        },
        {
          id: 'express',
          name: 'Express Shipping',
          description: 'Delivery in 2-3 business days',
          deliveryDays: '2-3 days',
          cost: 25.00,
        },
        {
          id: 'overnight',
          name: 'Overnight Shipping',
          description: 'Delivery next business day',
          deliveryDays: '1 day',
          cost: 50.00,
        },
      ],
      paymentMethods: [],
      
      totals: {
        subtotal: 0,
        shipping: { amount: 0, method: '' },
        tax: { amount: 0, details: [] },
        discounts: { amount: 0, items: [], coupons: [] },
        total: 0,
        currency: 'USD',
      },

      couponCode: '',
      couponApplied: false,
      processing: false,
      error: null,
      
      orderDetails: {
        orderNumber: '',
        orderId: null,
        total: 0,
      },
    };
  },

  computed: {
    couponCodes() {
      return this.totals.discounts?.coupons || [];
    },
  },

  async mounted() {
    await this.loadCartItems();
    await this.loadPaymentMethods();
    await this.loadShippingMethods();
    await this.calculateTotals();
  },

  methods: {
    async loadCartItems() {
      try {
        // Load cart items from local storage or API
        const cart = JSON.parse(localStorage.getItem('checkout_cart') || '[]');
        this.cartItems = cart;
      } catch (error) {
        this.error = 'Failed to load cart items';
      }
    },

    async loadPaymentMethods() {
      try {
        const response = await fetch('/api/checkout/payment-methods', {
          headers: {
            'Authorization': `Bearer ${this.$auth?.token || ''}`,
            'Accept': 'application/json',
          },
        });
        const data = await response.json();
        if (data.success) {
          this.paymentMethods = data.data;
        }
      } catch (error) {
        console.error('Failed to load payment methods:', error);
      }
    },

    async loadShippingMethods() {
      try {
        const response = await fetch('/api/checkout/shipping-methods', {
          headers: {
            'Authorization': `Bearer ${this.$auth?.token || ''}`,
            'Accept': 'application/json',
          },
        });
        const data = await response.json();
        if (data.success) {
          this.shippingMethods = data.data;
        }
      } catch (error) {
        console.error('Failed to load shipping methods:', error);
      }
    },

    async calculateTotals() {
      try {
        const response = await fetch('/api/checkout/calculate', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.$auth?.token || ''}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            items: this.cartItems,
            shipping_address: this.form.shippingAddress,
            billing_address: this.form.billingAddressSameAsShipping 
              ? this.form.shippingAddress 
              : this.form.billingAddress,
            coupon_codes: this.couponCodes,
          }),
        });
        const data = await response.json();
        if (data.success) {
          this.totals = data.data;
        }
      } catch (error) {
        this.error = 'Failed to calculate totals';
      }
    },

    async applyCoupon() {
      if (!this.couponCode.trim()) {
        this.error = 'Please enter a coupon code';
        return;
      }

      try {
        const response = await fetch('/api/checkout/validate-coupon', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.$auth?.token || ''}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ coupon_code: this.couponCode }),
        });
        const data = await response.json();

        if (data.valid) {
          this.couponApplied = true;
          this.totals.discounts.coupons.push(this.couponCode);
          await this.calculateTotals();
          this.couponCode = '';
        } else {
          this.error = data.message || 'Invalid coupon code';
        }
      } catch (error) {
        this.error = 'Failed to validate coupon';
      }
    },

    validateShippingAddress() {
      const address = this.form.shippingAddress;
      if (!address.firstName || !address.street || !address.city || !address.state) {
        this.error = 'Please fill in all required fields';
        return false;
      }
      this.nextStep();
      return true;
    },

    validateBillingAddress() {
      if (!this.form.billingAddressSameAsShipping) {
        const address = this.form.billingAddress;
        if (!address.firstName || !address.street || !address.city) {
          this.error = 'Please fill in all required fields';
          return false;
        }
      }
      return true;
    },

    handleBillingAddressToggle() {
      if (this.form.billingAddressSameAsShipping) {
        this.form.billingAddress = { ...this.form.shippingAddress };
      }
    },

    nextStep() {
      if (this.currentStep < this.steps.length - 1) {
        this.currentStep++;
      }
    },

    previousStep() {
      if (this.currentStep > 0) {
        this.currentStep--;
      }
    },

    async submitCheckout() {
      this.processing = true;
      this.error = null;

      try {
        const billingAddress = this.form.billingAddressSameAsShipping
          ? this.form.shippingAddress
          : this.form.billingAddress;

        const response = await fetch('/api/checkout/process', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.$auth?.token || ''}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            items: this.cartItems,
            shipping_address: this.form.shippingAddress,
            billing_address: billingAddress,
            payment_method: this.form.paymentMethod,
            coupon_codes: this.couponCodes,
            notes: '',
          }),
        });

        const data = await response.json();

        if (data.success) {
          // Store order details and show confirmation
          this.orderDetails = {
            orderNumber: data.data.order_number,
            orderId: data.data.order_id,
            total: data.data.totals.total,
          };

          // Clear cart
          localStorage.removeItem('checkout_cart');

          // Go to confirmation step
          this.currentStep = 4;
        } else {
          this.error = data.message || 'Failed to process checkout';
        }
      } catch (error) {
        this.error = error.message || 'An error occurred during checkout';
      } finally {
        this.processing = false;
      }
    },

    formatCurrency(amount) {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: this.totals.currency || 'USD',
      }).format(amount);
    },

    getPaymentIcon(code) {
      const icons = {
        square: 'icon-square',
        stripe: 'icon-stripe',
        paypal: 'icon-paypal',
        cod: 'icon-box',
      };
      return icons[code] || 'icon-credit-card';
    },
  },
};
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.checkout-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
  background: #f8f9fa;
  min-height: 100vh;
}

.checkout-header {
  text-align: center;
  margin-bottom: 40px;
}

.checkout-header h1 {
  font-size: 32px;
  margin: 0 0 10px 0;
  color: #333;
}

.subtitle {
  color: #666;
  font-size: 16px;
  margin: 0;
}

.checkout-content {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 30px;
  margin-bottom: 30px;
}

@media (max-width: 768px) {
  .checkout-content {
    grid-template-columns: 1fr;
  }
}

/* Steps Indicator */
.steps-indicator {
  display: flex;
  justify-content: space-between;
  margin-bottom: 40px;
  position: relative;
}

.steps-indicator::before {
  content: '';
  position: absolute;
  top: 20px;
  left: 0;
  right: 0;
  height: 2px;
  background: #ddd;
  z-index: -1;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.step-number {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: white;
  border: 2px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #999;
  transition: all 0.3s ease;
}

.step.active .step-number,
.step.completed .step-number {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.step-label {
  font-size: 12px;
  color: #999;
  text-align: center;
}

.step.active .step-label,
.step.completed .step-label {
  color: #333;
  font-weight: 500;
}

/* Form Sections */
.form-section {
  background: white;
  padding: 30px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.form-section h2 {
  font-size: 20px;
  margin: 0 0 20px 0;
  color: #333;
}

.form-section h3 {
  font-size: 16px;
  margin: 20px 0 10px 0;
  color: #333;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 20px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 500;
  margin-bottom: 5px;
  color: #333;
  font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="tel"],
.form-group input[type="email"] {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-group.checkbox {
  flex-direction: row;
  align-items: center;
  gap: 10px;
}

.form-group.checkbox input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.form-group.checkbox label {
  margin: 0;
  cursor: pointer;
}

/* Shipping Methods */
.shipping-methods {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.shipping-option {
  border: 2px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 15px;
}

.shipping-option:hover {
  border-color: #007bff;
  background: #f0f7ff;
}

.shipping-option.selected {
  border-color: #007bff;
  background: #f0f7ff;
}

.shipping-option input[type="radio"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  flex-shrink: 0;
}

.method-details {
  flex: 1;
}

.method-details h3 {
  margin: 0 0 5px 0;
  color: #333;
}

.method-details .description {
  margin: 0;
  color: #666;
  font-size: 13px;
}

.method-details .delivery-time {
  margin: 5px 0 0 0;
  color: #999;
  font-size: 12px;
}

.method-price {
  font-weight: bold;
  color: #333;
  font-size: 16px;
  white-space: nowrap;
}

/* Payment Methods */
.payment-methods {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
  margin-bottom: 20px;
}

.payment-option {
  border: 2px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 15px;
}

.payment-option:hover {
  border-color: #007bff;
  background: #f0f7ff;
}

.payment-option.selected {
  border-color: #007bff;
  background: #f0f7ff;
}

.payment-option input[type="radio"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  flex-shrink: 0;
}

.method-icon {
  font-size: 28px;
  width: 40px;
  text-align: center;
  flex-shrink: 0;
}

.method-info {
  flex: 1;
}

.method-info h3 {
  margin: 0 0 5px 0;
  color: #333;
}

.method-info p {
  margin: 0;
  color: #666;
  font-size: 13px;
}

/* Coupon Section */
.coupon-section {
  background: #f0f7ff;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.coupon-section h3 {
  margin: 0 0 10px 0;
}

.coupon-input-group {
  display: flex;
  gap: 10px;
}

.coupon-input-group input {
  flex: 1;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.coupon-success {
  color: #28a745;
  font-size: 14px;
  margin-top: 10px;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 30px;
}

.form-actions .btn {
  flex: 1;
}

/* Buttons */
.btn {
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-primary:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-secondary {
  background: white;
  color: #333;
  border: 1px solid #ddd;
}

.btn-secondary:hover {
  background: #f8f9fa;
  border-color: #999;
}

.btn-large {
  padding: 14px 24px;
  font-size: 16px;
}

/* Order Summary */
.order-summary {
  background: white;
  border-radius: 8px;
  padding: 20px;
  height: fit-content;
  position: sticky;
  top: 20px;
}

.summary-header {
  border-bottom: 1px solid #ddd;
  padding-bottom: 15px;
  margin-bottom: 15px;
}

.summary-header h2 {
  font-size: 18px;
  margin: 0;
  color: #333;
}

.summary-items {
  margin-bottom: 15px;
  max-height: 300px;
  overflow-y: auto;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #eee;
  font-size: 13px;
}

.item-name {
  margin: 0;
  color: #333;
  font-weight: 500;
}

.item-qty {
  margin: 3px 0 0 0;
  color: #999;
  font-size: 12px;
}

.item-info {
  flex: 1;
}

.item-price {
  color: #333;
  font-weight: 600;
  text-align: right;
}

.summary-divider {
  height: 1px;
  background: #ddd;
  margin: 15px 0;
}

.summary-totals {
  margin-bottom: 15px;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 13px;
  color: #666;
}

.total-row.discount {
  color: #28a745;
}

.total-row.total {
  border-top: 1px solid #ddd;
  padding-top: 10px;
  font-size: 16px;
  font-weight: bold;
  color: #333;
}

.security-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px;
  background: #f0f7ff;
  border-radius: 4px;
  font-size: 12px;
  color: #007bff;
  margin-top: 15px;
}

/* Success State */
.form-section.success {
  text-align: center;
}

.success-icon {
  font-size: 60px;
  color: #28a745;
  margin-bottom: 20px;
}

.form-section.success h2 {
  color: #28a745;
  margin-bottom: 10px;
}

.order-details {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin: 20px 0;
  text-align: left;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 10px 0;
  border-bottom: 1px solid #ddd;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row span {
  color: #666;
}

.detail-row strong {
  color: #333;
}

/* Error Message */
.error-message {
  position: fixed;
  bottom: 20px;
  left: 20px;
  background: #f8d7da;
  color: #721c24;
  padding: 15px 20px;
  border-radius: 4px;
  border: 1px solid #f5c6cb;
  max-width: 400px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1000;
}

.close-btn {
  background: none;
  border: none;
  color: #721c24;
  font-size: 20px;
  cursor: pointer;
  margin-left: 15px;
}
</style>
