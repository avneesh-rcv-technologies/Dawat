<?php

namespace Modules\Acl\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Services\CustomerSocialAuthService;

class CustomerSocialAuthApiController extends Controller
{
    protected CustomerSocialAuthService $socialAuthService;

    public function __construct(CustomerSocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * Authenticate with Google
     * POST /api/customer/auth/google
     */
    public function google(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_token' => 'required|string',
            ]);

            $result = $this->socialAuthService->authenticateWithGoogle($request->id_token);

            return response()->json($result, $result['is_new_user'] ? 201 : 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Authenticate with Apple
     * POST /api/customer/auth/apple
     */
    public function apple(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'identity_token' => 'required|string',
                'authorization_code' => 'nullable|string',
                'user' => 'nullable|array',
                'user.name' => 'nullable|array',
                'user.name.firstName' => 'nullable|string',
                'user.name.lastName' => 'nullable|string',
            ]);

            $result = $this->socialAuthService->authenticateWithApple(
                $request->identity_token,
                $request->authorization_code,
                $request->user
            );

            return response()->json($result, $result['is_new_user'] ? 201 : 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Link social account to existing customer
     * POST /api/customer/auth/link/{provider}
     */
    public function link(Request $request, string $provider): JsonResponse
    {
        try {
            $customer = Auth::guard('sanctum')->user();

            $request->validate([
                'id_token' => 'required|string',
            ]);

            if (!in_array($provider, ['google', 'apple'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid provider',
                ], 422);
            }

            $result = $this->socialAuthService->linkSocialAccount(
                $customer,
                $provider,
                $request->id_token
            );

            return response()->json($result, 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Unlink social account from customer
     * DELETE /api/customer/auth/unlink/{provider}
     */
    public function unlink(Request $request, string $provider): JsonResponse
    {
        try {
            $customer = Auth::guard('sanctum')->user();

            if (!in_array($provider, ['google', 'apple'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid provider',
                ], 422);
            }

            $result = $this->socialAuthService->unlinkSocialAccount($customer, $provider);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }

    /**
     * Get linked social accounts
     * GET /api/customer/auth/social-accounts
     */
    public function socialAccounts(Request $request): JsonResponse
    {
        try {
            $customer = Auth::guard('sanctum')->user();
            $socialAccounts = $customer->socialAccounts;

            return response()->json([
                'success' => true,
                'data' => $socialAccounts->map(function ($account) {
                    return [
                        'provider' => $account->provider,
                        'provider_email' => $account->provider_email,
                        'last_login_at' => $account->last_login_at,
                        'linked_at' => $account->created_at,
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
