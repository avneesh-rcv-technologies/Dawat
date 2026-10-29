<?php

namespace Modules\Restaurant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Models\Restaurant;

class RestaurantActiveMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1: Authentication
        |--------------------------------------------------------------------------
        */

        $admin = $request->user('sanctum');

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Step 2: Find Restaurant
        |--------------------------------------------------------------------------
        */
        $restaurant = Restaurant::query()
            ->where('admin_id', $admin->id)
            ->first();

        if (!$restaurant) {
            $admin->tokens()->delete();
            return response()->json([
                'success' => false,
                'message' => 'Restaurant profile not found.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Step 3: Normalize Statuses
        |--------------------------------------------------------------------------
        */

        $accountStatus = $restaurant->status instanceof \BackedEnum
            ? $restaurant->status->value
            : (string) ($restaurant->status ?? '');

        $approvalStatus = $restaurant->approval_status instanceof \BackedEnum
            ? $restaurant->approval_status->value
            : (string) ($restaurant->approval_status ?? '');

        $onboardingStatus = $restaurant->onboarding_status instanceof \BackedEnum
            ? $restaurant->onboarding_status->value
            : (string) ($restaurant->onboarding_status ?? '');


        /*
        |--------------------------------------------------------------------------
        | Common Status
        |--------------------------------------------------------------------------
        */

        $status = [
            'account_status' => $accountStatus,
            'approval_status' => $approvalStatus,
            'onboarding_status' => $onboardingStatus,
        ];


        /*
        |--------------------------------------------------------------------------
        | Step 4: Account Status
        |--------------------------------------------------------------------------
        |
        | draft    -> Restaurant account is not activated yet
        | inactive -> Restaurant has been deactivated
        | active   -> Account is active
        |
        */
        if ($accountStatus === 'inactive') {
            $admin->tokens()->delete();
            return response()->json([
                'success' => false,
                'message' => 'Your restaurant account has been deactivated. Please contact support for assistance.',
                'status' => $status,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Step 5: Draft Account
        |--------------------------------------------------------------------------
        |
        | Restaurant is still creating/submitting onboarding.
        |
        */

        if ($accountStatus === 'draft' && $onboardingStatus === OnboardingStatus::SUBMITTED->value) {
            return response()->json([
                'success' => true,
                'message' => 'Your restaurant account is not active / Kyc approval is in progress.',
                'status' => $status,
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Step 6: Unknown Account Status
        |--------------------------------------------------------------------------
        */

        if (!in_array($accountStatus, ['draft', 'active', 'inactive'], true)) {
            $admin->tokens()->delete();
            return response()->json([
                'success' => false,
                'message' => 'Invalid restaurant account status.',
                'status' => $status,
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Step 7: Approval Status
        |--------------------------------------------------------------------------
        |
        | pending  -> Admin approval/KYC approval pending
        | approved -> Admin approved
        |
        */

        if ($approvalStatus === 'pending') {
            return response()->json([
                'success' => true,
                'message' => 'Your restaurant approval is pending. Please wait for admin approval.',
                'status' => $status,
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | Step 8: Invalid Approval Status
        |--------------------------------------------------------------------------
        */
        if ($approvalStatus !== 'approved') {
            $admin->tokens()->delete();
            return response()->json([
                'success' => false,
                'message' => 'Your restaurant approval status is invalid.',
                'status' => $status,
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Step 9: Onboarding Status
        |--------------------------------------------------------------------------
        */

        switch ($onboardingStatus) {

            /*
            |--------------------------------------------------------------------------
            | Draft
            |--------------------------------------------------------------------------
            */

            case OnboardingStatus::DRAFT->value:

                return response()->json([
                    'success' => true,
                    'message' => 'Please complete your restaurant onboarding.',
                    'status' => $status,
                ], 200);


            /*
            |--------------------------------------------------------------------------
            | Submitted
            |--------------------------------------------------------------------------
            */

            case OnboardingStatus::SUBMITTED->value:

                return response()->json([
                    'success' => true,
                    'message' => 'Your restaurant onboarding has been submitted and is waiting for review.',
                    'status' => $status,
                ], 200);


            /*
            |--------------------------------------------------------------------------
            | Under Review
            |--------------------------------------------------------------------------
            */

            case OnboardingStatus::UNDER_REVIEW->value:

                return response()->json([
                    'success' => true,
                    'message' => 'Your restaurant onboarding is currently under review.',
                    'status' => $status,
                ], 200);


            /*
            |--------------------------------------------------------------------------
            | Action Required
            |--------------------------------------------------------------------------
            */

            case OnboardingStatus::ACTION_REQUIRED->value:

                return response()->json([
                    'success' => true,
                    'message' => 'Action is required on your restaurant onboarding. Please update the requested information or documents.',
                    'status' => $status,
                ], 200);


            /*
            |--------------------------------------------------------------------------
            | Rejected
            |--------------------------------------------------------------------------
            */
            case OnboardingStatus::REJECTED->value:
                return response()->json([
                    'success' => true,
                    'message' => 'Your restaurant onboarding has been rejected. Please contact support or resubmit the required information.',
                    'status' => $status,
                ], 200);

            /*
            |--------------------------------------------------------------------------
            | Suspended
            |--------------------------------------------------------------------------
            */
            case OnboardingStatus::SUSPENDED->value:
                $admin->tokens()->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Your restaurant onboarding has been suspended. Please contact support.',
                    'status' => $status,
                ], 401);


            /*
            |--------------------------------------------------------------------------
            | Approved
            |--------------------------------------------------------------------------
            |
            | Continue.
            |
            */

            case OnboardingStatus::APPROVED->value:

                break;


            /*
            |--------------------------------------------------------------------------
            | Active
            |--------------------------------------------------------------------------
            |
            | If onboarding itself becomes ACTIVE, allow access too.
            |
            */

            case OnboardingStatus::ACTIVE->value:

                break;


            /*
            |--------------------------------------------------------------------------
            | Unknown onboarding status
            |--------------------------------------------------------------------------
            */

            default:

                $admin->tokens()->delete();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid restaurant onboarding status.',
                    'status' => $status,
                ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Step 10: Everything is Valid
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}