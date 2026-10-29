<?php

namespace Modules\Acl\Http\Controllers\Api;

use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Services\CustomerAuthService;
use Illuminate\Validation\Rule;


class CustomerAuthApiController extends Controller
{
    protected $customerAuthService;

    public function __construct(CustomerAuthService $customerAuthService)
    {
        $this->customerAuthService = $customerAuthService;
    }

    /**
     * Customer Login API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $result = $this->customerAuthService->login($request->only('email', 'password'));

            if (isset($result['verification_required']) && $result['verification_required']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'verification_required' => true,
                    'data' => $result,
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $result
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [
                    'email' => [$e->getMessage()]
                ]
            ]);
        }
    }

    /**
     * Customer Registration API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers,email',
                'password' => ['required', 'string', 'confirmed', new StrongPassword()],
                'phone' => 'nullable|string|max:20',
            ]);

            $result = $this->customerAuthService->register($request->only('name', 'email', 'password', 'phone'));

            if (isset($result['verification_required']) && $result['verification_required']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'verification_required' => true,
                    'data' => $result,
                ], 201);
            }

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => $result
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Customer Logout API
     * 
     * @param Request $request
     * @return JsonResponse
     */

    // public function logout(Request $request): JsonResponse
    // {
    //     try {
    //         $customer = Auth::user();
    //         $result = $this->customerAuthService->logout($customer);

    //         return response()->json([
    //             'success' => true,
    //             'message' => $result['message']
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function logout(Request $request): JsonResponse
    {
        try {
            // $request->user()->currentAccessToken()->delete();
            Auth::guard('customer')->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Get Customer Profile API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            // $customer = Auth::user()->load('addresses');
            $customer = Auth::guard('customer')->user()->load('addresses');
            \Log::info('Customer Profile Accessed', ['customer ' => $customer]);
            $photo = fn_get_image('profile_photo', $customer->id);
            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone ?? null,
                        'created_at' => $customer->created_at,
                        'updated_at' => $customer->updated_at,
                        'model' => 'customer',
                        'profile_photo' => $photo['url'] ?? null,
                    ],
                    'addresses' => $customer->addresses->map(function ($address) {
                        return [
                            'id' => $address->id,
                            'city' => $address->city->name ?? null,
                            'state' => $address->state->name ?? null,
                            'postal_code' => $address->zip_code ?? null,
                            'country' => $address->country->name ?? null,
                            'is_default' => $address->is_default,
                            'address_type' => $address->address_type ?? null,
                            'address_line_1' => $address->address_line_1 ?? null,
                            'address_line_2' => $address->address_line_2 ?? null
                        ];
                    })
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Customer Profile API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    // public function updateProfile(Request $request): JsonResponse
    // {
    //     try {
    //         $customer = Auth::user();

    //         $request->validate([
    //             'name' => 'required|string|max:255',
    //             'email' => 'required|email|unique:customers,email,' . $customer->id,
    //             'phone' => 'nullable|string|max:20',
    //             'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    //         ]);

    //         if ($request->hasFile('image')) {
    //             fn_upload_image($request->file('image'), 'profile_photo', $customer->id, true);
    //         }

    //         $result = $this->customerAuthService->updateProfile(
    //             $customer,
    //             $request->only('name', 'email', 'phone')
    //         );


    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Profile updated successfully',
    //             'data' => $result
    //         ], 200);
    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {
    //         $statusCode = $e->getCode() ?: 500;
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], $statusCode);
    //     }
    // }


    public function updateProfile(Request $request): JsonResponse
    {
        try {
            // $customer = Auth::user();
            // $customer = $request->user();
            $customer = Auth::guard('customer')->user();

            $request->validate([
                'name'  => 'required|string|max:255',
                'email' => [
                    'sometimes',
                    'filled',
                    'email',
                    Rule::unique('customers', 'email')->ignore($customer->id),
                ],
                'phone' => 'nullable|string|max:20',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);


            if ($request->hasFile('image')) {
                fn_upload_image($request->file('image'), 'profile_photo', $customer->id, true);
            }

            $result = $this->customerAuthService->updateProfile(
                $customer,
                $request->only('name', 'email', 'phone')
            );

            // Fresh customer data with updated image
            $customer = $customer->fresh();
            $photo = fn_get_image('profile_photo', $customer->id);
            $data = [
                'id'    => $customer->id,
                'name'  => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'profile_photo' => $photo['url'] ?? null,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data'    => $data
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
                // 'errors'  => $e->errors()
            ], 500);
        }
    }

    /**
     * Change Customer Password API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            // $customer = Auth::user();
            // $customer = $request->user();
            $customer = Auth::guard('customer')->user();

            $request->validate([
                'current_password' => 'required|string',
                'new_password' => ['required', 'string', 'confirmed', new StrongPassword()],
            ]);

            $result = $this->customerAuthService->changePassword(
                $customer,
                $request->only('current_password', 'new_password')
            );

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ], 200);
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


    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email', 'exists:customers,email'],
                'type' => ['required', Rule::in(['forget_password'])],
                'otp' => ['required', 'string', 'size:4'],
                'password' => ['required', 'string', new StrongPassword(), 'confirmed'],
            ]);

            $result = $this->customerAuthService->resetPasswordByEmail(
                $validated['email'],
                $validated['password'],
                $validated['otp']
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['otp'][0] ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('Customer reset password error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to reset password.',
            ], 500);
        }
    }
}
