<?php

namespace Modules\Website\Support;

use Illuminate\Support\HtmlString;

class WebsiteImages
{
    private static function entry(string $source): ?array
    {
        // Normalize local asset() URLs passed by existing card components.
        $source = str_starts_with($source, asset('/')) ? substr($source, strlen(asset('/'))) : $source;
        static $manifest;
        if (!is_file(public_path('website/optimized/manifest.json'))) {
            return null;
        }
        $manifest ??= json_decode(file_get_contents(public_path('website/optimized/manifest.json')), true, 512, JSON_THROW_ON_ERROR);

        // Existing view data may already contain a versioned asset URL.
        static $renditions;
        if ($renditions === null) {
            $renditions = [];
            foreach ($manifest as $entry) {
                foreach ($entry['variants'] as $variant) {
                    $renditions[$variant['path']] = $entry;
                }
            }
        }

        return $manifest[ltrim($source, '/')] ?? $renditions[ltrim($source, '/')] ?? null;
    }

    public static function url(string $source, int $width = 1600): string
    {
        $entry = self::entry($source);
        if (!$entry) {
            return preg_match('~^https?://~', $source) ? $source : asset($source);
        }
        $variants = $entry['variants'];
        $selected = $variants[array_key_last($variants)];
        foreach ($variants as $variant) {
            if ($variant['width'] >= $width) {
                $selected = $variant;
                break;
            }
        }

        return asset($selected['path']);
    }

    public static function attributes(string $source, string $sizes = '100vw', int $width = 960): HtmlString
    {
        $attributes = ['src' => self::url($source, $width)];
        if ($entry = self::entry($source)) {
            $attributes += [
                'srcset' => implode(', ', array_map(fn ($v) => asset($v['path']).' '.$v['width'].'w', $entry['variants'])),
                'sizes' => $sizes,
                'width' => $entry['width'],
                'height' => $entry['height'],
            ];
        }

        return new HtmlString(implode(' ', array_map(fn ($key, $value) => $key.'="'.e($value).'"', array_keys($attributes), $attributes)));
    }
}
