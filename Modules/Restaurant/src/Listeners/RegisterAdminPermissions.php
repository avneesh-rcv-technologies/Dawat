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
                'settings.restaurant.working-hours.index' => 'View Restaurant Working Hours in Settings',
                'settings.restaurant.working-hours.update' => 'Update Restaurant Working Hours in Settings',
                'settings.restaurant.holidays.index' => 'View Restaurant Holiday Management in Settings',
                'settings.restaurant.holidays.update' => 'Update Restaurant Holiday Management in Settings',
                'settings.restaurant.preferences.index' => 'View Restaurant Settings in Settings',
                'settings.restaurant.preferences.update' => 'Update Restaurant Settings in Settings',
                'settings.restaurant.delivery-policy.index' => 'View Order Delivery Policy in Settings',
                'settings.restaurant.delivery-policy.update' => 'Update Order Delivery Policy in Settings',
                'settings.restaurant.gallery.index' => 'View Restaurant Gallery in Settings',
                'settings.restaurant.general-information.index' => 'View Restaurant General Information in Settings',
                'settings.restaurant.general-information.update' => 'Update Restaurant General Information in Settings',
                'settings.restaurant.general-information.address' => 'Update Restaurant Address in Settings',
                'settings.restaurant.dietary-certificates.index' => 'View Dietary Certificates in Settings',
                'settings.restaurant.dietary-certificates.store' => 'Upload Dietary Certificates in Settings',
                'settings.restaurant.gallery.store' => 'Upload Restaurant Gallery in Settings',
                'settings.restaurant.gallery.destroy' => 'Delete Restaurant Gallery in Settings',
                'admin.restaurant.update.address' => 'Update Restaurant Address',
                'admin.restaurant.update.holidays' => 'Update Restaurant Holidays',
                'admin.restaurant.update.settings' => 'Update Restaurant Settings',
                'admin.restaurant.bulk-delete' => 'Bulk Delete Restaurants',
                'admin.restaurant.bulk-status' => 'Bulk Update Restaurant Status',
                'admin.restaurant.gallery.store' => 'Upload Restaurant Gallery',
                'admin.restaurant.gallery.destroy' => 'Delete Restaurant Gallery',
                'admin.restaurant.approvals.index' => 'View Restaurant Approvals',
                'admin.restaurant.approvals.show' => 'View Restaurant Application Details',
                'admin.restaurant.approvals.status.update' => 'Update Restaurant Active / Inactive Status',
                'admin.restaurant.approvals.review.pdf' => 'Download Restaurant Onboarding Review PDF',
                'admin.restaurant.approvals.start-review' => 'Start Restaurant Onboarding Step Review',
                'admin.restaurant.approvals.request-changes' => 'Request Onboarding Corrections or Reverification',
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
