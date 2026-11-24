<?php

use Modules\Cache\Services\AppCache;

if (! function_exists('fn_clear_security_cache')) {
    function fn_clear_security_cache(): void
    {
        AppCache::flush('security');
    }
}
