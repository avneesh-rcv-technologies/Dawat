<?php

namespace Modules\MobileConfiguration\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * Mobile Configuration - Banners
         */
        $event->registerArray([
            'name' => 'Mobile Configuration',
            'code' => 'mobile_banners',
            'key' => [
                'mobileconfiguration.banners.index' => 'View Mobile Banners',
                'mobileconfiguration.banners.create' => 'Create Mobile Banners',
                'mobileconfiguration.banners.store' => 'Store Mobile Banners',
                'mobileconfiguration.banners.show' => 'Show Mobile Banners',
                'mobileconfiguration.banners.edit' => 'Edit Mobile Banners',
                'mobileconfiguration.banners.update' => 'Update Mobile Banners',
                'mobileconfiguration.banners.destroy' => 'Delete Mobile Banners',
                'mobileconfiguration.banners.bulk-delete' => 'Bulk Delete Mobile Banners',
                'mobileconfiguration.banners.toggle-status' => 'Toggle Mobile Banner Status',
            ]
        ]);

        /**
         * Mobile Configuration - Features
         */
        $event->registerArray([
            'name' => 'Mobile Configuration',
            'code' => 'mobile_features',
            'key' => [
                'mobileconfiguration.features.index' => 'View Mobile Features',
                'mobileconfiguration.features.create' => 'Create Mobile Features',
                'mobileconfiguration.features.store' => 'Store Mobile Features',
                'mobileconfiguration.features.show' => 'Show Mobile Features',
                'mobileconfiguration.features.edit' => 'Edit Mobile Features',
                'mobileconfiguration.features.update' => 'Update Mobile Features',
                'mobileconfiguration.features.destroy' => 'Delete Mobile Features',
                'mobileconfiguration.features.bulk-delete' => 'Bulk Delete Mobile Features',
                'mobileconfiguration.features.toggle-status' => 'Toggle Mobile Feature Status',
            ]
        ]);

        /**
         * Mobile Configuration - Walkthroughs
         */
        $event->registerArray([
            'name' => 'Mobile Configuration',
            'code' => 'mobile_walkthroughs',
            'key' => [
                'mobileconfiguration.walkthroughs.index' => 'View Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.create' => 'Create Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.store' => 'Store Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.show' => 'Show Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.edit' => 'Edit Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.update' => 'Update Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.destroy' => 'Delete Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.bulk-delete' => 'Bulk Delete Mobile Walkthroughs',
                'mobileconfiguration.walkthroughs.toggle-status' => 'Toggle Mobile Walkthrough Status',
            ]
        ]);

        /**
         * Mobile Configuration - CMS
         */
        $event->registerArray([
            'name' => 'Mobile Configuration',
            'code' => 'mobile_cms',
            'key' => [
                'mobileconfiguration.cms.index' => 'View Mobile CMS',
                'mobileconfiguration.cms.create' => 'Create Mobile CMS',
                'mobileconfiguration.cms.store' => 'Store Mobile CMS',
                'mobileconfiguration.cms.show' => 'Show Mobile CMS',
                'mobileconfiguration.cms.edit' => 'Edit Mobile CMS',
                'mobileconfiguration.cms.update' => 'Update Mobile CMS',
                'mobileconfiguration.cms.destroy' => 'Delete Mobile CMS',
                'mobileconfiguration.cms.bulk-delete' => 'Bulk Delete Mobile CMS',
                'mobileconfiguration.cms.toggle-status' => 'Toggle Mobile CMS Status',
            ]
        ]);

        /**
         * Mobile Configuration - Homepage Layouts
         */
        $event->registerArray([
            'name' => 'Mobile Configuration',
            'code' => 'mobile_homepage_layouts',
            'key' => [
                'mobileconfiguration.homepage-layouts.index' => 'View Homepage Layouts',
                'mobileconfiguration.homepage-layouts.create' => 'Create Homepage Layouts',
                'mobileconfiguration.homepage-layouts.store' => 'Store Homepage Layouts',
                'mobileconfiguration.homepage-layouts.show' => 'Show Homepage Layouts',
                'mobileconfiguration.homepage-layouts.edit' => 'Edit Homepage Layouts',
                'mobileconfiguration.homepage-layouts.update' => 'Update Homepage Layouts',
                'mobileconfiguration.homepage-layouts.destroy' => 'Delete Homepage Layouts',
                'mobileconfiguration.homepage-layouts.bulk-delete' => 'Bulk Delete Homepage Layouts',
                'mobileconfiguration.homepage-layouts.toggle-status' => 'Toggle Homepage Layout Status',
                'mobileconfiguration.homepage-layouts.set-default' => 'Set Homepage Layout as Default',
            ]
        ]);
    }
}