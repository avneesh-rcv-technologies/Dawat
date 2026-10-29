<?php

namespace Modules\Checkout\Services;

use Exception;
use Modules\Payments\Services\PaymentsProcessorRegistry;
use Modules\Payments\Models\PaymentMethod;

class PaymentMethodService
{
    protected $paymentsProcessorRegistry;

    public function __construct(PaymentsProcessorRegistry $paymentsProcessorRegistry)
    {
        $this->paymentsProcessorRegistry = $paymentsProcessorRegistry;
    }

    /**
     * Get available payment methods using processors
     *
     * @return array
     */
    public function getPaymentMethods(): array
    {
        try {
            // Get active payment methods from database
            $paymentMethods = PaymentMethod::where('is_active', true)->get();
            
            $methods = [];
            foreach ($paymentMethods as $paymentMethod) {
                // Check if processor is registered
                if ($this->paymentsProcessorRegistry->has($paymentMethod->code)) {
                    $processor = $this->paymentsProcessorRegistry->get($paymentMethod->code);
                    
                    $methods[] = [
                        'id' => $paymentMethod->code,
                        'name' => $paymentMethod->name,
                        'description' => $this->getMethodDescription($paymentMethod->code),
                        'icon' => $this->getMethodIcon($paymentMethod->code),
                        'enabled' => $paymentMethod->is_active,
                        'processor_name' => $processor->getName(),
                        'template' => $paymentMethod->template,
                        'processor_template' => $paymentMethod->processor
                    ];
                }
            }
            
            // If no methods found, return fallback
            if (empty($methods)) {
                return $this->getFallbackMethods();
            }
            
            return $methods;
            
        } catch (Exception $e) {
            // Fallback to basic methods if processor registry fails
            return $this->getFallbackMethods();
        }
    }

    /**
     * Get method description based on code
     */
    private function getMethodDescription(string $code): string
    {
        $descriptions = [
            'cod' => 'Pay with cash when your order arrives',
            'cash_on_delivery' => 'Pay with cash when your order arrives',
            'card' => 'Pay securely with your credit/debit card',
            'digital_wallet' => 'Pay with your digital wallet',
            'stripe' => 'Pay securely with Stripe',
            'paypal' => 'Pay with PayPal',
        ];

        return $descriptions[$code] ?? 'Payment method';
    }

    /**
     * Get method icon based on code
     */
    private function getMethodIcon(string $code): string
    {
        $icons = [
            'cod' => 'cash',
            'cash_on_delivery' => 'cash',
            'card' => 'card',
            'digital_wallet' => 'wallet',
            'stripe' => 'stripe',
            'paypal' => 'paypal',
        ];

        return $icons[$code] ?? 'payment';
    }

    /**
     * Fallback methods if processor registry fails
     */
    private function getFallbackMethods(): array
    {
        return [
            [
                'id' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay with cash when your order arrives',
                'icon' => 'cash',
                'enabled' => true
            ],
            [
                'id' => 'card',
                'name' => 'Credit/Debit Card',
                'description' => 'Pay securely with your card',
                'icon' => 'card',
                'enabled' => true
            ],
            [
                'id' => 'digital_wallet',
                'name' => 'Digital Wallet',
                'description' => 'Pay with your digital wallet',
                'icon' => 'wallet',
                'enabled' => true
            ]
        ];
    }

    /**
     * Validate payment method using processor registry
     *
     * @param string $method
     * @return bool
     * @throws Exception
     */
    public function validatePaymentMethod(string $method): bool
    {
        try {
            // Check if processor exists in registry
            if (!$this->paymentsProcessorRegistry->has($method)) {
                throw new Exception('Invalid payment method');
            }

            // Check if payment method is active in database
            $paymentMethod = PaymentMethod::where('code', $method)
                ->where('is_active', true)
                ->first();

            if (!$paymentMethod) {
                throw new Exception('Payment method not available');
            }

            return true;
            
        } catch (Exception $e) {
            // Fallback validation for basic methods (include both cod and cash_on_delivery)
            $validMethods = ['cod', 'cash_on_delivery', 'card', 'digital_wallet', 'stripe', 'paypal'];
            
            if (!in_array($method, $validMethods)) {
                throw new Exception('Invalid payment method');
            }

            return true;
        }
    }
}