<?php

// Laravel's development router otherwise serves existing SEO files directly.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

if (in_array($uri, ['/sitemap.xml', '/robots.txt'], true)) {
    // Existing files otherwise look like the script/base URL to Symfony's request parser.
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/public/index.php';
    require __DIR__.'/public/index.php';
} else {
    // Pass false back to PHP's server so it serves existing static assets.
    return require __DIR__.'/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';
}
