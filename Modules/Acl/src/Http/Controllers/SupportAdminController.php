<?php

namespace Modules\Acl\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Acl\DataView\SupportMessagesGrid;
use Modules\Acl\Services\SupportAdminService;
use Illuminate\Support\Facades\Validator;

class SupportAdminController extends Controller
{
    protected $supportAdminService;

    public function __construct(SupportAdminService $supportAdminService)
    {
        $this->supportAdminService = $supportAdminService;
    }

    /**
     * Display a listing of support messages
     */
    public function index(Request $request)
    {
        $lists = fn_datagrid(SupportMessagesGrid::class)->process();
        $statistics = $this->supportAdminService->getStatistics();
        
        return view('acl::pages.support.index', compact('lists', 'statistics'));
    }

    /**
     * Display the specified support message
     */
    public function show($id)
    {
        $message = $this->supportAdminService->find($id);
        
        if (!$message) {
            return redirect()->route('support.index')->with('error', 'Support message not found.');
        }

        // Mark as read if it's new
        if ($message->status === 'new') {
            $this->supportAdminService->updateStatus($id, 'read');
            $message = $this->supportAdminService->find($id);
        }

        return view('acl::pages.support.show', compact('message'));
    }

    /**
     * Update the status of the specified support message
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,read,replied,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $message = $this->supportAdminService->updateStatus($id, $request->status);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'data' => $message
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Remove the specified support message
     */
    public function destroy($id)
    {
        try {
            $this->supportAdminService->delete($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Support message deleted successfully!',
                'redirect_url' => route('support.index'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'errors' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    /**
     * Update status for multiple messages
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:support_messages,id',
            'action' => 'required|in:new,read,replied,closed'
        ]);

        try {
            $updatedCount = $this->supportAdminService->updateStatuses($request->ids, $request->action);
            return redirect()->route('support.index')->with('success', 'Status Updated Successfully');
        } catch (\Throwable $e) {
            return redirect()->route('support.index')->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Delete multiple messages
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:support_messages,id',
        ]);

        try {
            $deletedCount = $this->supportAdminService->deleteMultiple($request->ids);
            return redirect()->route('support.index')->with('success', 'Bulk Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->route('support.index')->with('error', 'Something went wrong. Please try again.');
        }
    }
}
