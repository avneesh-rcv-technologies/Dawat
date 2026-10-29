<?php

namespace Modules\KYC\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\KYC\DataView\ComplianceRecordGrid;
use Modules\KYC\DataView\ComplianceMissingGrid;
use Modules\Acl\Models\Admin;
use Modules\KYC\Models\ComplianceRecord;
use Modules\KYC\Models\KycType;
use Modules\KYC\Services\ComplianceService;
use Exception;

class ComplianceController extends Controller
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Display listing of compliance records.
     */
    public function index(Request $request)
    {
        try {
            $lists = fn_datagrid(ComplianceRecordGrid::class)->process();

            $statistics = [
                'total'         => ComplianceRecord::count(),
                'active'        => ComplianceRecord::where('status', 'active')->count(),
                'expiring_soon' => ComplianceRecord::where('status', 'expiring_soon')->count(),
                'expired'       => ComplianceRecord::where('status', 'expired')->count(),
            ];

            return view('kyc::admin.compliance.index', compact('lists', 'statistics'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load compliance records: ' . $e->getMessage());
        }
    }

    /**
     * Manually send a reminder for a compliance record.
     */
    public function remind($id)
    {
        try {
            $record = ComplianceRecord::find($id);

            if (!$record) {
                return back()->with('error', 'Compliance record not found.');
            }

            $sent = $this->complianceService->sendManualReminder($record);

            if (!$sent) {
                return back()->with('error', 'Reminder not sent: account has no email address.');
            }

            return back()->with('success', 'Reminder sent.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to send reminder: ' . $e->getMessage());
        }
    }

    /**
     * Bulk reminder for selected compliance records.
     */
    public function remindBulk(Request $request)
    {
        try {
            $ids     = array_filter((array) $request->input('ids', []));
            $sent    = 0;
            $skipped = 0;

            foreach ($ids as $id) {
                $record = ComplianceRecord::find($id);
                if ($record) {
                    $this->complianceService->sendManualReminder($record) ? $sent++ : $skipped++;
                }
            }

            return back()->with('success', $this->bulkMessage($sent, $skipped));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to send reminders: ' . $e->getMessage());
        }
    }

    /**
     * Display restaurant owners who have not uploaded their compliance documents.
     */
    public function missing(Request $request)
    {
        try {
            $lists = fn_datagrid(ComplianceMissingGrid::class)->process();

            $missingCount = $this->complianceService->missingQueryBuilder()->count();

            return view('kyc::admin.compliance.missing', compact('lists', 'missingCount'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load missing compliance list: ' . $e->getMessage());
        }
    }

    /**
     * Manually remind a single restaurant owner about a missing compliance document.
     */
    public function remindMissing($owner, $type)
    {
        try {
            $ownerModel = Admin::find($owner);
            $typeModel   = KycType::where('id', $type)->where('type', 'compliance')->first();

            if (!$ownerModel || !$typeModel) {
                return back()->with('error', 'Restaurant Owner or compliance type not found.');
            }

            $sent = $this->complianceService->sendMissingReminder($ownerModel, $typeModel);

            if (!$sent) {
                return back()->with('error', 'Reminder not sent: account has no email address.');
            }

            return back()->with('success', 'Reminder sent.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to send reminder: ' . $e->getMessage());
        }
    }

    /**
     * Bulk reminder for selected restaurant_owners who are missing compliance.
     * Each selected id is a "ownerId-typeId" pair.
     */
    public function remindMissingBulk(Request $request)
    {
        try {
            $ids     = array_filter((array) $request->input('ids', []));
            $sent    = 0;
            $skipped = 0;

            foreach ($ids as $pair) {
                [$ownerId, $typeId] = array_pad(explode('-', (string) $pair, 2), 2, null);

                $owner = Admin::find($ownerId);
                $type   = KycType::where('id', $typeId)->where('type', 'compliance')->first();

                if ($owner && $type) {
                    $this->complianceService->sendMissingReminder($owner, $type) ? $sent++ : $skipped++;
                }
            }

            return back()->with('success', $this->bulkMessage($sent, $skipped));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to send reminders: ' . $e->getMessage());
        }
    }

    /**
     * Build a user-facing message for bulk reminders.
     */
    protected function bulkMessage(int $sent, int $skipped): string
    {
        $message = "Reminder sent to {$sent} account(s).";

        if ($skipped > 0) {
            $message .= " {$skipped} skipped (no email address).";
        }

        return $message;
    }
}
