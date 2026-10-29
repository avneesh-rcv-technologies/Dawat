<?php

namespace Modules\KYC\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event): void
    {
        $event->registerArray([
            'name' => 'KYC',
            'code' => 'kyc',
            'guard' => ['admin'],
            'key' => [
                'admin.kyc.types.index' => 'View KYC Types',
                'admin.kyc.types.create' => 'Create KYC Types',
                'admin.kyc.types.store' => 'Store KYC Types',
                'admin.kyc.types.edit' => 'Edit KYC Types',
                'admin.kyc.types.update' => 'Update KYC Types',
                'admin.kyc.types.destroy' => 'Delete KYC Types',
                'admin.kyc.types.bulk-delete' => 'Bulk Delete KYC Types',
                'admin.kyc.types.fields' => 'View KYC Fields',
                'admin.kyc.types.add-field' => 'Add KYC Fields',
                'admin.kyc.types.update-field' => 'Update KYC Fields',
                'admin.kyc.types.delete-field' => 'Delete KYC Fields',
                'admin.kyc.types.reorder-fields' => 'Reorder KYC Fields',

                'admin.kyc.submissions.index' => 'View KYC Submissions',
                'admin.kyc.submissions.show' => 'View KYC Submission Details',
                'admin.kyc.submissions.review' => 'Review KYC Submissions',
                'admin.kyc.submissions.bulk-review' => 'Bulk Review KYC Submissions',
                'admin.kyc.submissions.preview-file' => 'Preview KYC Submission Files',
                'admin.kyc.submissions.download-file' => 'Download KYC Submission Files',
                'admin.kyc.submissions.add-message' => 'Add KYC Submission Messages',

                'admin.kyc.compliance.index' => 'View KYC Compliance Records',
                'admin.kyc.compliance.remind' => 'Send KYC Compliance Reminders',
                'admin.kyc.compliance.remind-bulk' => 'Send Bulk KYC Compliance Reminders',
                'admin.kyc.compliance.missing' => 'View Missing KYC Compliance',
                'admin.kyc.compliance.remind-missing' => 'Send Missing KYC Compliance Reminders',
                'admin.kyc.compliance.remind-missing-bulk' => 'Send Bulk Missing KYC Compliance Reminders',

                'admin.kyc.requests.index' => 'View KYC Requests',
                'admin.kyc.requests.create' => 'Create KYC Requests',
                'admin.kyc.requests.store' => 'Store KYC Requests',
                'admin.kyc.requests.destroy' => 'Delete KYC Requests',
                'admin.kyc.requests.bulk-destroy' => 'Bulk Delete KYC Requests',
                'admin.kyc.requests.get-users' => 'Find Users for KYC Requests',
            ],
        ]);
    }
}
