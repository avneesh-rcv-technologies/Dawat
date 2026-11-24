<?php

namespace Modules\Faq\Listeners;

use Modules\Core\Events\RegisterPermissions;

class RegisterFaqPermissions
{
    public function handle(RegisterPermissions $event)
    {
        $event->registerArray([
            'name' => 'FAQ',
            'code' => 'faq',
            'key' => [
                'admin.faqs.index' => 'View FAQs',
                'admin.faqs.create' => 'Create FAQs',
                'admin.faqs.store' => 'Store FAQs',
                'admin.faqs.show' => 'Show FAQs',
                'admin.faqs.edit' => 'Edit FAQs',
                'admin.faqs.update' => 'Update FAQs',
                'admin.faqs.destroy' => 'Delete FAQs',
                'admin.faqs.bulk-delete' => 'Bulk Delete FAQs',
                'admin.faqs.toggle-status' => 'Toggle FAQ Status',
                'admin.faqs.group.edit' => 'Edit FAQ Groups',
                'admin.faqs.group.update' => 'Update FAQ Groups',
                'admin.faqs.group.destroy' => 'Delete FAQ Groups',
            ],
        ]);
    }
}
