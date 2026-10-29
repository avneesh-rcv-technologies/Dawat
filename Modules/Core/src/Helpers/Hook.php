<?php

namespace Modules\Core\Helpers;

use Exception;
use Google\ApiCore\ApiException;
use Illuminate\Support\Facades\Log;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

class Hook
{
    public static function render($hookName, $vars = [])
    {
        if (!preg_match('/^([a-z0-9_]+)\:([a-z0-9_\-]+)\.(pre|post|override)$/i', $hookName, $m)) {
            return '';
        }

        $module = strtolower($m[1]);
        $file = strtolower($m[2]);
        $type = strtolower($m[3]);

        $output = '';

        $modulesPath = base_path('Modules');

        $enabledModules = [];
        foreach (glob($modulesPath . '/*/module.json') as $moduleJsonFile) {
            $moduleDir = dirname($moduleJsonFile);
            $moduleName = basename($moduleDir);

            try {
                $moduleData = json_decode(file_get_contents($moduleJsonFile), true);
                if (isset($moduleData['enabled']) && $moduleData['enabled'] === true) {
                    $enabledModules[] = strtolower($moduleName);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Error reading module.json for {$moduleName}: " . $e->getMessage());
            }
        }


        try {
            foreach (scandir($modulesPath) as $mod) {
             
                if ($mod === '.' || $mod === '..')
                    continue;

                if (!in_array(strtolower($mod), $enabledModules)) {
                    continue;
                }

                $hookFile = "$modulesPath/$mod/src/Resources/views/hooks/$module/$file.$type.blade.php";

                if (file_exists($hookFile)) {
                    $output .= view()->file($hookFile, $vars)->render();  // PUSH VARS HERE
                }
            }
        } catch (Exception $e) {
            ApiExceptionHandler::handle($e);
        }


        return $output;
    }

}
