<?php

return [
    /**
     * Public URL segment where theme assets live.
     * By default this points to `public/themes` and the helper
     * will generate URLs like `/themes/{theme}/css/app.css`.
     *
     * You can change this if you deploy assets to a different
     * public path or CDN (e.g. `cdn/themes` or full URL).
     */
    'assets_url' => env('THEME_ASSETS_URL', 'themes'),
];
