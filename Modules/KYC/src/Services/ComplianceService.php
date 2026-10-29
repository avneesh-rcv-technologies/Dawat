<?php

namespace Modules\KYC\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Acl\Models\Role;
use Modules\KYC\Models\ComplianceRecord;
use Modules\KYC\Models\KycType;
use Modules\KYC\Models\KycSubmission;
use Modules\Notifications\Services\NotificationService;

class ComplianceService
{
    /** period key => months */
    public const PERIODS = [
        '1_month'  => 1,
        '3_months' => 3,
        '6_months' => 6,
        '1_year'   => 12,
    ];

    /**
     * All compliance-type ids (identification is by kyc_types.type).
     */
    public function complianceTypeIds(): array
    {
        return KycType::compliance()->pluck('id')->all();
    }

    /**
     * Is the given kyc_type_id a compliance type?
     */
    public function isComplianceType(int $kycTypeId): bool
    {
        return $kycTypeId > 0
            && KycType::whereKey($kycTypeId)->where('type', 'compliance')->exists();
    }

    public function periodKey(): string
    {
        $p = fn_get_setting('compliance.period', '1_year');
        return array_key_exists($p, self::PERIODS) ? $p : '1_year';
    }

    public function periodMonths(): int
    {
        return self::PERIODS[$this->periodKey()];
    }

    public function reminderDays(): int
    {
        return (int) fn_get_setting('compliance.reminder_days', 15);
    }

    /**
     * Called when a COMPLIANCE submission is approved.
     * Creates or refreshes the single compliance_records row for this restaurant owner + type.
     */
    public function openRecord(KycSubmission $submission): ComplianceRecord
    {
        $months  = $this->periodMonths();
        $from    = Carbon::today();
        $expires = (clone $from)->addMonths($months)->endOfDay();

        $record = ComplianceRecord::updateOrCreate(
            ['user_id' => $submission->user_id, 'kyc_type_id' => $submission->kyc_type_id],
            [
                'submission_id'       => $submission->id,
                'valid_from'          => $from->toDateString(),
                'expires_at'          => $expires,
                'validity_period'     => $this->periodKey(),
                'status'              => 'active',
                'reminder_sent_at'    => null,
                'expired_notified_at' => null,
            ]
        );

        $this->sendApproved($record->fresh());

        Log::info('Compliance record opened', [
            'record_id'  => $record->id,
            'owner_id'  => $record->user_id,
            'expires_at' => $record->expires_at,
        ]);

        return $record;
    }

    /**
     * On re-upload after expiry: clear expiry so status API shows is_expired = false
     * until admin re-approves (openRecord then sets a fresh validity window).
     */
    public function clearExpiryOnReupload(int $ownerId, int $kycTypeId, int $submissionId): void
    {
        $record = ComplianceRecord::where('user_id', $ownerId)
            ->where('kyc_type_id', $kycTypeId)
            ->first();

        if (!$record) {
            return;
        }

        if (!$record->isExpired() && $record->status !== 'expired') {
            return;
        }

        $record->update([
            'submission_id'       => $submissionId,
            'expires_at'          => null,
            'valid_from'          => null,
            'reminder_sent_at'    => null,
            'expired_notified_at' => null,
            'status'              => 'expired', // stays until re-approved; date cleared so is_expired is false
        ]);
    }

    /**
     * Marks expiring/expired records and sends notifications.
     * Expired status is always updated; expired notifications re-fire once per day
     * until the restaurant owner renews (openRecord resets expired_notified_at).
     */
    public function processExpiries(): array
    {
        $now      = Carbon::now();
        $today    = Carbon::today();
        $reminder = $now->copy()->addDays($this->reminderDays());
        $expired  = 0;
        $expiring = 0;

        // always flip past-due rows to expired (independent of notify flag)
        ComplianceRecord::where('status', '!=', 'expired')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->chunkById(200, function ($records) {
                foreach ($records as $record) {
                    $record->update(['status' => 'expired']);
                }
            });

        //once per calendar day while still past due
        ComplianceRecord::where('status', 'expired')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->where(function ($q) use ($today) {
                $q->whereNull('expired_notified_at')
                    ->orWhereDate('expired_notified_at', '<', $today);
            })
            ->chunkById(200, function ($records) use (&$expired) {
                foreach ($records as $record) {
                    $this->sendExpired($record->fresh());
                    $record->update(['expired_notified_at' => now()]);
                    $expired++;
                }
            });

        //  EXPIRING SOON — within reminder window, reminder not yet sent today
        ComplianceRecord::whereIn('status', ['active', 'expiring_soon'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $reminder)
            ->where(function ($q) use ($today) {
                $q->whereNull('reminder_sent_at')
                    ->orWhereDate('reminder_sent_at', '<', $today);
            })
            ->chunkById(200, function ($records) use (&$expiring) {
                foreach ($records as $record) {
                    $record->update([
                        'status'           => 'expiring_soon',
                        'reminder_sent_at' => now(),
                    ]);
                    $this->sendExpiringSoon($record->fresh());
                    $expiring++;
                }
            });

        return ['expired' => $expired, 'expiring_soon' => $expiring];
    }

    /**
     * Manually send a reminder for a single record (admin-triggered from the grid).
     * Returns false (skipped) when the restaurant owner has no email address.
     */
    public function sendManualReminder(ComplianceRecord $record): bool
    {
        if (!$this->ownerHasEmail($record->owner)) {
            return false;
        }

        if ($record->isExpired() || $record->status === 'expired') {
            $this->sendExpired($record);
            $record->update([
                'status'              => 'expired',
                'expired_notified_at' => now(),
            ]);
        } else {
            $this->sendExpiringSoon($record);
            $record->update([
                'status'           => 'expiring_soon',
                'reminder_sent_at' => now(),
            ]);
        }

        return true;
    }

    /* -------- restaurant owners WITHOUT compliance -------- */

    /**
     * Base query for approved restaurant owners that are missing an (approved) compliance
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function missingQueryBuilder()
    {
        $ownerRoleId = Role::query()
            ->where('guard', 'admin')
            ->where('name', 'Restaurant Owner')
            ->value('id');

        return DB::table('admins')
            ->crossJoin('kyc_types')
            ->leftJoin('compliance_records', function ($join) {
                $join->on('compliance_records.user_id', '=', 'admins.id')
                    ->on('compliance_records.kyc_type_id', '=', 'kyc_types.id');
            })
            ->where('kyc_types.type', 'compliance')
            ->where('kyc_types.is_active', 1)
            ->when($ownerRoleId, fn ($q) => $q->where('admins.role_id', $ownerRoleId))
            ->where('admins.status', 1)
            ->where('admins.onboarding_outcome', 'approved')
            ->whereNull('compliance_records.id');
    }

    /**
     * Notify a single restaurant owner that a compliance document is missing.
     * Returns false (skipped) when the restaurant owner has no email address.
     */
    public function sendMissingReminder($owner, KycType $type): bool
    {
        if (!$this->ownerHasEmail($owner)) {
            return false;
        }

        $this->notifier()->trigger('KYC', 'ComplianceMissing', $owner, [
            'owner_name'   => $owner->name ?? 'Restaurant Owner',
            'document_type' => $type->name ?? 'Compliance',
            'company_name'  => config('app.name'),
            'current_year'  => date('Y'),
        ]);

        return true;
    }

    /* -------- notifications  -------- */

    protected function notifier(): NotificationService
    {
        return app(NotificationService::class);
    }

    /**
     * A restaurant owner can only be notified when it has an email address; the shared
     * email channel builds a null recipient otherwise and aborts the dispatch.
     */
    protected function ownerHasEmail($owner): bool
    {
        return $owner && !empty($owner->email);
    }

    protected function baseVars(ComplianceRecord $record): array
    {
        $record->loadMissing(['owner', 'kycType']);
        $owner = $record->owner;

        return [
            'owner_name'     => $owner->name ?? 'Restaurant Owner',
            'document_type'   => $record->kycType->name ?? 'Compliance',
            'valid_from'      => optional($record->valid_from)->format('Y-m-d'),
            'expires_at'      => optional($record->expires_at)->format('Y-m-d'),
            'validity_period' => str_replace('_', ' ', (string) $record->validity_period),
            'company_name'    => config('app.name'),
            'current_year'    => date('Y'),
        ];
    }

    protected function sendApproved(ComplianceRecord $record): void
    {
        if (!$this->ownerHasEmail($record->owner)) {
            return;
        }
        $this->notifier()->trigger('KYC', 'ComplianceStatus', $record->owner, $this->baseVars($record));
    }

    protected function sendExpiringSoon(ComplianceRecord $record): void
    {
        if (!$this->ownerHasEmail($record->owner)) {
            return;
        }
        $this->notifier()->trigger('KYC', 'ComplianceExpiringSoon', $record->owner, $this->baseVars($record));
    }

    protected function sendExpired(ComplianceRecord $record): void
    {
        if (!$this->ownerHasEmail($record->owner)) {
            return;
        }
        // mapping has notify_owner = true AND notify_admin = true
        $this->notifier()->trigger('KYC', 'ComplianceExpired', $record->owner, $this->baseVars($record));
    }
}
