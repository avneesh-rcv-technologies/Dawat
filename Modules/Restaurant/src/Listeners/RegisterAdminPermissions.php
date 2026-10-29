<?php

namespace Modules\Restaurant\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Restaurant Management
         */
        $event->registerArray([
            'name' => 'restaurant',
            'code' => 'restaurant',
            'guard' => ['admin'],
            'key' => [
                'admin.restaurant.index' => 'View Restaurants',
                'admin.restaurant.create' => 'Create Restaurant',
                'admin.restaurant.edit' => 'Edit Restaurant',
                'admin.restaurant.view' => 'View Restaurant Details',
                'admin.restaurant.delete' => 'Delete Restaurant',
                'admin.restaurant.store.general' => 'Store Restaurant General Info',
                'admin.restaurant.update.hours' => 'Update Restaurant Hours',
                'admin.restaurant.update.address' => 'Update Restaurant Address',
                'admin.restaurant.update.holidays' => 'Update Restaurant Holidays',
                'admin.restaurant.update.settings' => 'Update Restaurant Settings',
                'admin.restaurant.bulk-delete' => 'Bulk Delete Restaurants',
                'admin.restaurant.bulk-status' => 'Bulk Update Restaurant Status',
                'admin.restaurant.gallery.store' => 'Upload Restaurant Gallery',
                'admin.restaurant.gallery.destroy' => 'Delete Restaurant Gallery',
                'admin.restaurant.approvals.index' => 'View Restaurant Approvals',
                'admin.restaurant.approvals.show' => 'View Restaurant Application Details',
                'admin.restaurant.approvals.status.update' => 'Update Restaurant Active / Inactive / Draft Status',
                'admin.restaurant.approvals.onboarding-status.update' => 'Update Restaurant Onboarding Status',
                'admin.restaurant.approvals.review.pdf' => 'Download Restaurant Onboarding Review PDF',
                'admin.restaurant.approvals.start-review' => 'Start Restaurant Onboarding Step Review',
                'admin.restaurant.approvals.request-changes' => 'Request Onboarding Corrections or Reverification',
                'admin.restaurant.approvals.menu.review' => 'Review Restaurant Menu Corrections',
                'admin.restaurant.approvals.approve' => 'Approve Restaurant Application',
                'admin.restaurant.approvals.reject' => 'Reject Restaurant Application',
                "admin.restaurant.approvals.registration.update" => "Edit Restaurant Registration Details",
                'admin.restaurant.approvals.registration.email-verification' => 'Manage Restaurant Owner Email Verification',
                'admin.restaurant.approvals.banking.show' => 'View Restaurant Banking and Company Details',
                'admin.restaurant.approvals.banking.update' => 'Edit Restaurant Banking and Company Details',
                'admin.restaurant.approvals.banking.approve' => 'Approve Restaurant Banking Details',
                'admin.restaurant.approvals.banking.reject' => 'Reject Restaurant Banking Details',
                'admin.restaurant.approvals.tax.show' => 'View Restaurant Tax Information',
                'admin.restaurant.approvals.tax.update' => 'Edit Restaurant Tax Information',
                'admin.restaurant.approvals.tax.type' => 'Change Restaurant Tax Entity Type',
                'admin.restaurant.approvals.tax.approve' => 'Approve Restaurant Tax Information',
                'admin.restaurant.approvals.tax.reject' => 'Reject Restaurant Tax Information',
            ]
        ]);
    }
}
