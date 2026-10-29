<?php

namespace Modules\Cms\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterAdminPermissions
{
    public function handle(RegisterPermissions $event)
    {
        /**
         * CMS - Pages
         */
        $event->registerArray([
            'name' => 'CMS',
            'code' => 'cms_pages',
            'key' => [
                'cms.pages.index' => 'View CMS Pages',
                'cms.pages.create' => 'Create CMS Pages',
                'cms.pages.store' => 'Store CMS Pages',
                'cms.pages.show' => 'Show CMS Pages',
                'cms.pages.edit' => 'Edit CMS Pages',
                'cms.pages.update' => 'Update CMS Pages',
                'cms.pages.destroy' => 'Delete CMS Pages',
                'cms.pages.bulk-delete' => 'Bulk Delete CMS Pages',
                'cms.pages.toggle-status' => 'Toggle CMS Page Status',
            ]
        ]);
    }
}