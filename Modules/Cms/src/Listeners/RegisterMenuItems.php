<?php

namespace Modules\Cms\Listeners;

use Modules\Core\Events\RegisterMenu;

class RegisterMenuItems
{
    public function handle(RegisterMenu $event): void
    {
        // $event->addItem([
        //     'label' => 'Website',
        //     'icon' => 'language',
        //     'guard' => 'admin',
        //     'permission' => 'cms.pages.index', 
        //     'position' => 50,
        //     'children' => [
        //         [
        //             'label' => 'CMS Pages',
        //             'route' => 'cms.pages.index',
        //             'icon' => 'article',
        //             'position' => 1,
        //             'permission' => 'cms.pages.index',  
        //             'guard' => 'admin',
        //         ],
        //     ]
        // ]);
    }
}
