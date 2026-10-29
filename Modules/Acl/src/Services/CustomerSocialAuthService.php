<?php

namespace Modules\Acl\Services;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Modules\Acl\Models\Customer;
use Modules\Acl\Models\CustomerSocialAccount;
use Modules\Notifications\Services\NotificationService;

class CustomerSocialAuthService
{

    protected NotificationService $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Authenticate customer with Google
     * 
     * @param string $idToken - Google ID token from mobile app
     * @return array
     */
    public function authenticateWithGoogle(string $idToken): array
    {
        return DB::transaction(function () use ($idToken) {
            // Verify Google ID token
            $googleUser = $this->verifyGoogleToken($idToken);

            if (!$googleUser) {
                throw new Exception('Invalid Google token', 401);
            }

            // Find or create customer
            return $this->findOrCreateSocialCustomer('google', $googleUser);
        });
    }

    /**
     * Authenticate customer with Apple
     * 
     * @param string $identityToken - Apple identity token from mobile app
     * @param string|null $authorizationCode - Apple authorization code
     * @param array|null $user - User data (only provided on first sign-in)
     * @return array
     */
    public function authenticateWithApple(
        string $identityToken,
        ?string $authorizationCode = null,
        ?array $user = null
    ): array {
        return DB::transaction(function () use ($identityToken, $authorizationCode, $user) {
            // Verify Apple identity token
            $appleUser = $this->verifyAppleToken($identityToken, $user);

            if (!$appleUser) {
                throw new Exception('Invalid Apple token', 401);
            }

            // Find or create customer
            return $this->findOrCreateSocialCustomer('apple', $appleUser);
        });
    }

    /**
     * Link social account to existing customer
     * 
     * @param Customer $customer
     * @param string $provider
     * @param string $idToken
     * @return array
     */
    public function linkSocialAccount(Customer $customer, string $provider, string $idToken): array
    {
        return DB::transaction(function () use ($customer, $provider, $idToken) {
            // Verify token based on provider
            if ($provider === 'google') {
                $socialUser = $this->verifyGoogleToken($idToken);
            } elseif ($provider === 'apple') {
                $socialUser = $this->verifyAppleToken($idToken);
            } else {
                throw new Exception('Invalid provider', 422);
            }

            if (!$socialUser) {
                throw new Exception('Invalid token', 401);
            }

            // Check if social account already linked to another customer
            $existingAccount = CustomerSocialAccount::forProvider($provider)
                ->forProviderId($socialUser['id'])
                ->first();

            if ($existingAccount && $existingAccount->customer_id !== $customer->id) {
                throw new Exception('This social account is already linked to another account', 422);
            }

            // Create or update social account
            $socialAccount = CustomerSocialAccount::updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'provider' => $provider,
                ],
                [
                    'provider_id' => $socialUser['id'],
                    'provider_email' => $socialUser['email'] ?? null,
                    'provider_token' => $socialUser['token'] ?? null,
                    'provider_data' => $socialUser,
                    'last_login_at' => Carbon::now(),
                ]
            );

            // Add auth method to customer
            $customer->addAuthMethod($provider);

            return [
                'success' => true,
                'message' => ucfirst($provider) . ' account linked successfully',
                'provider' => $provider,
            ];
        });
    }

    /**
     * Unlink social account from customer
     */
    public function unlinkSocialAccount(Customer $customer, string $provider): array
    {
        $socialAccount = $customer->socialAccount($provider);

        if (!$socialAccount) {
            throw new Exception('Social account not found', 404);
        }

        // Check if customer has other auth methods
        $authMethods = $customer->auth_methods ?? [];
        if (count($authMethods) <= 1) {
            throw new Exception('Cannot unlink the only authentication method', 422);
        }

        $socialAccount->delete();

        // Remove auth method from customer
        $authMethods = array_diff($authMethods, [$provider]);
        $customer->update(['auth_methods' => array_values($authMethods)]);

        // Update primary auth method if needed
        if ($customer->primary_auth_method === $provider) {
            $customer->setPrimaryAuthMethod($authMethods[0]);
        }

        return [
            'success' => true,
            'message' => ucfirst($provider) . ' account unlinked successfully',
        ];
    }

    /**
     * Find or create customer from social provider data
     */
    protected function findOrCreateSocialCustomer(string $provider, array $socialUser): array
    {
        // Check if social account already exists
        $socialAccount = CustomerSocialAccount::forProvider($provider)
            ->forProviderId($socialUser['id'])
            ->first();

        if ($socialAccount) {
            // Existing social account - login
            $customer = $socialAccount->customer;

            // Update last login
            $socialAccount->updateLastLogin();

            // Update token if provided
            if (isset($socialUser['token'])) {
                $socialAccount->update([
                    'provider_token' => $socialUser['token'],
                    'provider_data' => $socialUser,
                ]);
            }

            $isNewUser = false;
        } else {
            // Check if customer exists with same email
            $customer = null;
            if (!empty($socialUser['email'])) {
                $customer = Customer::where('email', $socialUser['email'])->first();
            }

            if ($customer) {
                // Link social account to existing customer
                $socialAccount = CustomerSocialAccount::create([
                    'customer_id' => $customer->id,
                    'provider' => $provider,
                    'provider_id' => $socialUser['id'],
                    'provider_email' => $socialUser['email'] ?? null,
                    'provider_token' => $socialUser['token'] ?? null,
                    'provider_data' => $socialUser,
                    'last_login_at' => Carbon::now(),
                ]);

                // Add auth method
                $customer->addAuthMethod($provider);

                $isNewUser = false;
            } else {
                // Create new customer
                $customer = Customer::create([
                    'name' => $socialUser['name'] ?? 'Customer',
                    'email' => $socialUser['email'] ?? null,
                    'email_verified' => !empty($socialUser['email_verified']),
                    'email_verified_at' => !empty($socialUser['email_verified']) ? Carbon::now() : null,
                    'avatar_url' => $socialUser['picture'] ?? null,
                    'auth_methods' => [$provider],
                    'primary_auth_method' => $provider,
                    'status' => true,
                ]);

                // Create social account
                $socialAccount = CustomerSocialAccount::create([
                    'customer_id' => $customer->id,
                    'provider' => $provider,
                    'provider_id' => $socialUser['id'],
                    'provider_email' => $socialUser['email'] ?? null,
                    'provider_token' => $socialUser['token'] ?? null,
                    'provider_data' => $socialUser,
                    'last_login_at' => Carbon::now(),
                ]);
                if (!empty($customer->email)) {
                    // Trigger CustomerRegistered notification (for customer)
                    $this->notificationService->trigger(
                        'Acl',
                        'CustomerRegistered',
                        $customer->email,
                        $customer
                    );
                }

                $isNewUser = true;
            }
        }

        // Check if customer is active
        if (!$customer->status) {
            throw new Exception('Account is not active', 403);
        }

        // Generate token
        $token = $customer->createToken('customer-' . $provider . '-token')->plainTextToken;

        return [
            'success' => true,
            'message' => $isNewUser ? 'Registration successful' : 'Login successful',
            'is_new_user' => $isNewUser,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar_url' => $customer->avatar_url,
                'email_verified' => $customer->email_verified,
                'phone_verified' => $customer->phone_verified,
                'auth_methods' => $customer->auth_methods,
                'primary_auth_method' => $customer->primary_auth_method,
                'created_at' => $customer->created_at,
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Verify Google ID token
     * 
     * @param string $idToken
     * @return array|null
     */
    protected function verifyGoogleToken(string $idToken): ?array
    {
        try {
            // Call Google's tokeninfo endpoint to verify the token
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken
            ]);

            if (!$response->successful()) {
                Log::error('Google token verification failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            // Verify the token is for your app
            // Collect all configured Google Client IDs (Android, iOS, Web)
            $allowedClientIds = [];

            $androidClientId = fn_get_setting('social_auth.google.android_client_id');
            if ($androidClientId) {
                $allowedClientIds[] = $androidClientId;
            }

            $iosClientId = fn_get_setting('social_auth.google.ios_client_id');
            if ($iosClientId) {
                $allowedClientIds[] = $iosClientId;
            }

            $webClientId = fn_get_setting('social_auth.google.web_client_id');
            if ($webClientId) {
                $allowedClientIds[] = $webClientId;
            }

            if (empty($allowedClientIds)) {
                Log::error('Google Client IDs not configured in settings');
                return null;
            }

            if (!in_array($data['aud'], $allowedClientIds)) {
                Log::error('Google token audience mismatch', [
                    'expected' => $allowedClientIds,
                    'received' => $data['aud']
                ]);
                return null;
            }

            // Return user data
            return [
                'id' => $data['sub'],
                'email' => $data['email'] ?? null,
                'name' => $data['name'] ?? null,
                'picture' => $data['picture'] ?? null,
                'email_verified' => $data['email_verified'] ?? false,
                'token' => $idToken,
            ];
        } catch (Exception $e) {
            Log::error('Google token verification exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }


    /**
     * Verify Apple identity token
     * 
     * @param string $identityToken
     * @param array|null $user - User data from Apple (only on first sign-in)
     * @return array|null
     */
    protected function verifyAppleToken(string $identityToken, ?array $user = null): ?array
    {
        try {
            // Decode the JWT token (without verification for now)
            // In production, you should verify the signature using Apple's public keys
            $parts = explode('.', $identityToken);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            if (!$payload) {
                return null;
            }

            // Verify the token is for your app
            $appleClientId = fn_get_setting('social_auth.apple.client_id');
            if (!$appleClientId) {
                Log::error('Apple Client ID not configured in settings');
                return null;
            }

            if ($payload['aud'] !== $appleClientId) {
                Log::error('Apple token audience mismatch', [
                    'expected' => $appleClientId,
                    'received' => $payload['aud']
                ]);
                return null;
            }

            // Check token expiry
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                Log::error('Apple token expired');
                return null;
            }

            // Extract user data
            $userData = [
                'id' => $payload['sub'],
                'email' => $payload['email'] ?? null,
                'email_verified' => $payload['email_verified'] ?? false,
                'token' => $identityToken,
            ];

            // Add name from user object (only provided on first sign-in)
            if ($user && isset($user['name'])) {
                $name = [];
                if (!empty($user['name']['firstName'])) {
                    $name[] = $user['name']['firstName'];
                }
                if (!empty($user['name']['lastName'])) {
                    $name[] = $user['name']['lastName'];
                }
                $userData['name'] = implode(' ', $name) ?: 'Customer';
            } else {
                $userData['name'] = 'Customer';
            }

            return $userData;
        } catch (Exception $e) {
            Log::error('Apple token verification exception', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
