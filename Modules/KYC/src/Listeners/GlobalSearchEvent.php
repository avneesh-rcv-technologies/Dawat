<?php

namespace Modules\KYC\Listeners;

use Modules\Core\Events\RegisterGlobalSearch;
use Modules\KYC\Models\KycSubmission;

class GlobalSearchEvent
{
    public function handle(RegisterGlobalSearch $event): void
    {
        $query = (string) $event->query;
        $moduleFilter = $event->module;

        // Register search preference
        $event->addSearchPreference('kyc', 'KYC');

        if (strlen($query) < 2) {
            return;
        }

        // KYC Submission Search
        if ($moduleFilter === 'all' || $moduleFilter === 'kyc') {



            // Option B: With join to search kyc_type name
            $kyc = KycSubmission::with('kycType', 'owner')
                ->join('kyc_types', 'kyc_submissions.kyc_type_id', '=', 'kyc_types.id')
                ->where(function ($q) use ($query) {
                    $q->where('kyc_submissions.status', 'LIKE', "%{$query}%")
                        ->orWhere('kyc_types.name', 'LIKE', "%{$query}%")
                        ->orWhere('kyc_submissions.rejection_reason', 'LIKE', "%{$query}%");
                })
                ->select('kyc_submissions.*')
                ->limit(5)
                ->get();

            foreach ($kyc as $k) {
                $ownerName = $k->owner ? $k->owner->first_name . ' ' . $k->owner->last_name : 'Unknown';
                $kycTypeName = $k->kycType->name ?? 'Unknown';

                $event->addResult([
                    'id' => $k->id,
                    'title' => $kycTypeName,
                    'url' => '/admin/kyc/submissions?filters[id]=' . $k->id,
                    'type' => '',
                    'module' => 'KYC',
                    'module_name' => 'KYC',
                ]);
            }
        }
    }
}
