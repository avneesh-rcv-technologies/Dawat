<?php

namespace Modules\Acl\Http\Controllers\Api;

use Modules\Acl\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Exception;

class GuestCheckoutApiController extends Controller
{
    public function generateToken(): JsonResponse
    {
        try {
            $guestEmail = 'guest-' . Str::uuid() . '@guest.local';
            $guestName = 'Guest-' . strtoupper(Str::random(8));
            $guestPhone = '0000000000';

            // Create guest customer
            $guest = Customer::create([
                'name' => $guestName,
                'email' => $guestEmail,
                'phone' => $guestPhone,
                'is_guest' => true,
                'status' => true,
                'password' => bcrypt(Str::random(32)),
            ]);

            // Generate  token
            $token = $guest->createToken('guest-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Guest token generated successfully',
                'data' => [
                    'token' => $token,
                    'guest_id' => $guest->id,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
