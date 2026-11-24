<?php

namespace Modules\Seo\Services;

use Modules\Seo\Support\SeoSettings;

class RobotsBuilder
{
    public function build(): string
    {
        if (SeoSettings::get('robots.use_custom', false)) {
            $custom = (string) SeoSettings::get('robots.custom_content', '');
            if ($custom !== '') {
                return rtrim($custom) . PHP_EOL;
            }
        }

        return $this->buildFromConfig();
    }

    public function buildFromConfig(): string
    {
        if (! SeoSettings::get('robots.enabled', config('seo.robots.enabled', true))) {
            return "User-agent: *\nDisallow:\n";
        }

        $lines = [];
        $userAgents = config('seo.robots.user_agents', []);

        foreach ($userAgents as $agent => $rules) {
            $lines[] = 'User-agent: ' . $agent;

            foreach ($rules['allow'] ?? [] as $path) {
                $lines[] = 'Allow: ' . $path;
            }

            foreach ($rules['disallow'] ?? [] as $path) {
                $lines[] = 'Disallow: ' . $path;
            }

            $lines[] = '';
        }

        $sitemap = config('seo.robots.sitemap', '{app_url}/sitemap.xml');
        $sitemap = str_replace('{app_url}', rtrim((string) config('app.url'), '/'), $sitemap);
        $lines[] = 'Sitemap: ' . $sitemap;

        return implode(PHP_EOL, array_filter($lines, fn ($line) => $line !== '')) . PHP_EOL;
    }
}
