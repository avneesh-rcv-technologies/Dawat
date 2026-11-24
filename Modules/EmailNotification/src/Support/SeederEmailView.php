<?php

namespace Modules\EmailNotification\Support;

use Illuminate\Contracts\View\View;

class SeederEmailView
{
    public static function make(string $name, array $data = []): View
    {
        $views = view();
        if (! $views->exists($name)) {
            $namespace = explode('::', $name, 2)[0];
            $module = ['restaurant' => 'Restaurant', 'website' => 'Website', 'kyc' => 'KYC', 'acl' => 'Acl', 'dietarymanagment' => 'DietaryManagment', 'emailnotification' => 'EmailNotification'][$namespace] ?? null;
            if ($module !== null) {
                // Console seeders can run without the module's service provider.
                // Keep registered/published overrides ahead of the bundled fallback.
                $views->addNamespace($namespace, [
                    resource_path('views/modules/'.$namespace),
                    base_path('Modules/'.$module.'/src/Resources/views'),
                ]);
            }
        }

        return $views->make($name, $data);
    }
}
