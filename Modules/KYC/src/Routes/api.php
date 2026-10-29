<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| KYC API Routes
|--------------------------------------------------------------------------
*/

// API Routes for User-side KYC Submission
Route::prefix('api/kyc')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/types', function() {
        $types = \Modules\KYC\Models\KycType::active()->with('fields')->get();
        return response()->json($types);
    });
    
    Route::post('/submit', function(\Illuminate\Http\Request $request) {
        $service = app(\Modules\KYC\Services\KycSubmissionService::class);
        
        try {
            $submission = $service->createSubmission(
                auth()->id(),
                $request->input('kyc_type_id'),
                $request->all()
            );
            
            return response()->json([
                'success' => true,
                'message' => 'KYC submission created successfully',
                'data' => $submission
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    });
    
    Route::get('/submissions', function() {
        $service = app(\Modules\KYC\Services\KycSubmissionService::class);
        $submissions = $service->getSubmissions(['user_id' => auth()->id()]);
        
        return response()->json($submissions);
    });
}); 