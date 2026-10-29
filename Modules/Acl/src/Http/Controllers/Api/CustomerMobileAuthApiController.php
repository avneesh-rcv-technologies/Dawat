<?php

namespace Modules\Acl\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Acl\Services\CustomerOtpService;

class CustomerMobileAuthApiController extends Controller
{
    protected CustomerOtpService $otpService;

    public function __construct(CustomerOtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP (Unified for both login and registration)
     * POST /api/customer/auth/send-otp
     */
    public function sendOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => ['nullable', 'string', 'required_without:email'],
                'email' => ['nullable', 'email', 'required_without:phone'],
                'type' => ['nullable', 'string'],
                'channel' => ['nullable', 'string', 'in:sms,email'],
            ], [
                'phone.required_without' => 'Phone or email is required.',
                'email.required_without' => 'Phone or email is required.',
            ]);

            $identifier = $request->input('email') ?: $request->input('phone');
            $result = $this->otpService->sendOtp($identifier, $request->input('type'), $request->input('channel'));

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
     * Verify OTP (Unified for both login and registration)
     * POST /api/customer/auth/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => ['nullable', 'string', 'required_without:email'],
                'email' => ['nullable', 'email', 'required_without:phone'],
                'otp' => 'required|string|size:4',
                'type' => ['nullable', 'string'],
                'channel' => ['nullable', 'string', 'in:sms,email'],
            ], [
                'phone.required_without' => 'Phone or email is required.',
                'email.required_without' => 'Phone or email is required.',
            ]);

            $identifier = $request->input('email') ?: $request->input('phone');
            $result = $this->otpService->verifyOtp($identifier, $request->otp, $request->input('type'), $request->input('channel'));

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
     * Resend OTP
     * POST /api/customer/auth/resend-otp
     */
    public function resendOtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'phone' => ['nullable', 'string', 'required_without:email'],
                'email' => ['nullable', 'email', 'required_without:phone'],
                'type' => ['nullable', 'string'],
                'channel' => ['nullable', 'string', 'in:sms,email'],
            ], [
                'phone.required_without' => 'Phone or email is required.',
                'email.required_without' => 'Phone or email is required.',
            ]);

            $identifier = $request->input('email') ?: $request->input('phone');
            $result = $this->otpService->resendOtp($identifier, $request->input('type'), $request->input('channel'));

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
}
