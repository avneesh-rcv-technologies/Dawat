<?php

namespace Modules\KYC\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\KYC\Services\KycSubmissionService;
use Modules\KYC\Services\KycNotificationService;
use Modules\KYC\Http\Requests\KycSubmissionRequest;
use Modules\KYC\DataView\KycSubmissionGrid;
use Modules\KYC\Traits\HandlesKycErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Restaurant\Models\Restaurant;

class KycSubmissionController extends Controller
{
    use HandlesKycErrors;
    protected $submissionService;
    protected $notificationService;

    public function __construct(
        KycSubmissionService $submissionService,
        KycNotificationService $notificationService
    ) {
        $this->submissionService = $submissionService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display listing of submissions
     */
    public function index(Request $request)
    {
        try {
            if ($request->has('user_id')) {
                $firstSubmission = \Modules\KYC\Models\KycSubmission::where('user_id', $request->user_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if ($firstSubmission) {
                    return redirect()->route('admin.kyc.submissions.show', $firstSubmission->id);
                } else {
                    return redirect()->route('admin.kyc.submissions.index')->with('error', 'No submissions found for this restaurant owner.');
                }
            } else {
                $grid = new \Modules\KYC\DataView\KycSubmissionGroupedGrid();
                $view = 'kyc::admin.submissions.index_grouped';
            }

            $lists = $grid->process();
            $actor = $request->user('admin');
            $ownerOnly = app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($actor);
            $statistics = $this->submissionService->getStatistics($ownerOnly ? ['user_id' => $actor->id] : []);

            if ($request->ajax()) {
                return response()->json($lists);
            }

            return view($view, compact('lists', 'statistics'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load KYC submissions: ' . $e->getMessage());
        }
    }

    /**
     * Show submission details
     */
    public function show(Request $request, $id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return redirect()->route('admin.kyc.submissions.index')
                    ->with('error', 'Invalid submission ID');
            }

            $submission = $this->submissionService->getSubmission((int)$id);
            $userSubmissions = \Modules\KYC\Models\KycSubmission::with('kycType')
                ->where('user_id', $submission->user_id)
                ->orderBy('created_at', 'desc')
                ->get();

            $onboardingRestaurant = null;
            if (bouncer('admin')->hasPermission('admin.restaurant.approvals.show')
                && bouncer('admin')->hasPermission('admin.kyc.submissions.show')) {
                $validated = $request->validate(['restaurant_id' => ['nullable', 'integer', 'min:1']]);
                $onboardingRestaurant = Restaurant::where('admin_id', $submission->user_id)
                    ->when(isset($validated['restaurant_id']), fn ($query) => $query->whereKey($validated['restaurant_id']))
                    ->orderBy('id')->first();
            }

            return view('kyc::admin.submissions.show', compact('submission', 'userSubmissions', 'onboardingRestaurant'));
        } catch (Exception $e) {
            $message = $e instanceof \Modules\KYC\Exceptions\KycException && $e->getErrorCode() === 'SUBMISSION_NOT_FOUND'
                ? $e->getMessage()
                : 'We could not load this KYC submission. Please try again.';

            return redirect()->route('admin.kyc.submissions.index')
                ->with('error', $message);
        }
    }

    /**
     * Review submission
     */

    // old code
    // public function review(Request $request, $id)
    // {
    //     try {
    //         $request->validate([
    //             'action' => 'required|in:under_review,approve,reject,request_changes',
    //             'reason' => 'nullable|required_if:action,reject,request_changes|string|max:500'
    //         ]);

    //         if (!is_numeric($id) || $id <= 0) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid submission ID'
    //             ], 400);
    //         }

    //         $adminId = Auth::guard('admin')->id();
    //         if (!$adminId) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Authentication required'
    //             ], 401);
    //         }

    //         $submission = $this->submissionService->reviewSubmission(
    //             (int)$id,
    //             $adminId,
    //             $request->input('action'),
    //             $request->input('reason')
    //         );

    //         // Send notification
    //         try {
    //             $this->notificationService->sendKycStatusNotification($submission);
    //         } catch (Exception $notificationError) {
    //             // Log notification error but don't fail the review
    //             \Log::warning('Failed to send KYC status notification', [
    //                 'submission_id' => $id,
    //                 'error' => $notificationError->getMessage()
    //             ]);
    //         }

    //         return $this->handleKycSuccess($submission, 'Submission reviewed successfully');
    //     } catch (Exception $e) {
    //         return $this->handleKycException($e, 'Review Submission');
    //     }
    // }

    // new code
    public function bulkReview(Request $request)
    {
        if ($request->input('action') === 'request_changes') {
            abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($request->user('admin')), 403);
            if (is_string($request->input('reason'))) {
                $request->merge(['reason' => trim($request->input('reason'))]);
            }
        }
        try {
            $request->validate([
                'action' => 'required|in:under_review,approve,reject,request_changes',
                'reason' => 'nullable|required_if:action,reject,request_changes|string|max:500',
                'ids' => 'required|array'
            ]);

            $ids = array_filter((array) $request->input('ids', []));
            $action = $request->input('action');
            $reason = $request->input('reason');
            $adminId = Auth::guard('admin')->id();

            if (!$adminId) {
                return back()->with('error', 'Authentication required');
            }

            $processed = 0;
            $skipped = 0;

            foreach ($ids as $id) {
                if (!is_numeric($id)) {
                    $skipped++;
                    continue;
                }

                try {
                    $this->submissionService->reviewSubmission((int)$id, $adminId, $action, $reason);
                    $processed++;
                } catch (Exception $e) {
                    $skipped++;
                }
            }

            $message = "Performed '{$action}' on {$processed} submission(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} skipped.";
            }

            return back()->with('success', $message);
        } catch (Exception $e) {
            return $this->handleKycException($e, 'Bulk Review Submissions');
        }
    }


    public function review(Request $request, $id)
    {
        if ($request->input('action') === 'request_changes') {
            abort_if(app(\Modules\Restaurant\Services\RestaurantApprovalAccess::class)->isOwner($request->user('admin')), 403);
            if (is_string($request->input('reason'))) {
                $request->merge(['reason' => trim($request->input('reason'))]);
            }
        }
        try {
            $request->validate([
                'action' => 'required|in:under_review,approve,reject,request_changes',
                'reason' => 'nullable|required_if:action,reject,request_changes|string|max:500'
            ]);
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid submission ID'
                ], 400);
            }
            $adminId = Auth::guard('admin')->id();
            if (!$adminId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            $action = $request->input('action');

            // Service updates the submission and aggregate KYC onboarding step.
            $submission = $this->submissionService->reviewSubmission(
                (int)$id,
                $adminId,
                $action,
                $request->input('reason')
            );

            return $this->handleKycSuccess($submission, $action === 'request_changes'
                ? 'Changes requested. The owner can refill and reupload this document.' : 'Submission reviewed successfully');
        } catch (Exception $e) {
            return $this->handleKycException($e, 'Review Submission');
        }
    }



    /**
     * Download file
     */
    public function downloadFile($submissionId, $fileId)
    {
        try {
            if (!is_numeric($submissionId) || $submissionId <= 0) {
                abort(400, 'Invalid submission ID');
            }

            if (!is_numeric($fileId) || $fileId <= 0) {
                abort(400, 'Invalid file ID');
            }

            $submission = $this->submissionService->getSubmission((int)$submissionId);
            $file = $submission->files()->find($fileId);

            if (!$file) {
                abort(404, 'File not found');
            }

            $filePath = storage_path('app/public/' . $file->file_path);

            if (!file_exists($filePath)) {
                abort(404, 'File does not exist on disk');
            }

            return response()->download($filePath, $file->original_name ?? basename($file->file_path));
        } catch (Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500) {
                abort($e->getCode(), $e->getMessage());
            }
            abort(500, 'Failed to download file');
        }
    }


    /**
     * Preview file inline (for image/pdf preview)
     */
    public function previewFile($submissionId, $fileId)
    {
        try {
            if (!is_numeric($submissionId) || $submissionId <= 0) {
                abort(400, 'Invalid submission ID');
            }

            if (!is_numeric($fileId) || $fileId <= 0) {
                abort(400, 'Invalid file ID');
            }

            $submission = $this->submissionService->getSubmission((int)$submissionId);
            $file = $submission->files()->find($fileId);

            if (!$file) {
                abort(404, 'File not found');
            }

            $filePath = storage_path('app/public/' . $file->file_path);

            if (!file_exists($filePath)) {
                abort(404, 'File does not exist on disk');
            }

            // Use response()->file to attempt inline display in browser
            $headers = [];
            if (!empty($file->file_type)) {
                $headers['Content-Type'] = $file->file_type;
            }

            return response()->file($filePath, $headers);
        } catch (Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500) {
                abort($e->getCode(), $e->getMessage());
            }
            abort(500, 'Failed to preview file');
        }
    }



    /**
     * Add message to submission
     */
    public function addMessage(Request $request, $id)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000'
            ]);

            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid submission ID'
                ], 400);
            }

            $adminId = Auth::guard('admin')->id();
            if (!$adminId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $submission = $this->submissionService->getSubmission((int)$id);

            $message = $submission->messages()->create([
                'sender_type' => 'admin',
                'sender_id' => $adminId,
                'message' => $request->input('message'),
                'is_read' => false
            ]);

            // Send notification
            try {
                $this->notificationService->sendNewMessageNotification($submission, $message);
            } catch (Exception $notificationError) {
                // Log notification error but don't fail the message creation
                \Log::warning('Failed to send message notification', [
                    'submission_id' => $id,
                    'message_id' => $message->id,
                    'error' => $notificationError->getMessage()
                ]);
            }

            return $this->handleKycSuccess($message, 'Message added successfully');
        } catch (Exception $e) {
            return $this->handleKycException($e, 'Add Message');
        }
    }
}
