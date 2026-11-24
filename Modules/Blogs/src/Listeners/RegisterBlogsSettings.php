<?php

namespace Modules\Blogs\Listeners;

use Modules\Core\Events\RegisterSettingsMenu;

class RegisterBlogsSettings
{
    public function handle(RegisterSettingsMenu $event): void
    {


        $event->register([
            'category_code' => 'interface',
            'title' => 'Blogs',
            'description' => 'Manage blogs and blog categories.',
            'route' => route('admin.blogs.blogs.index'),
            'icon' => 'language',
            'permission' => 'admin.blogs.blogs.index',
        ]);


    }
}
