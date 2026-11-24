<?php

namespace Modules\Faq\Listeners;


use Modules\Core\Events\RegisterSettingsMenu;

class RegisterFaqMenu
{

    public function handle(RegisterSettingsMenu $event): void
    {


        $event->register([
            'category_code' => 'interface',
            'title' => 'FAQs',
            'description' => 'Manage FAQs and FAQ categories.',
            'route' => route('admin.faqs.index'),
            'icon' => 'help_outline',
            'permission' => 'admin.faqs.index',
        ]);

    }
}