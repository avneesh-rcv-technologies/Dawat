<?php

namespace Modules\Seo\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Seo\Support\SeoSettings;

class SchemaBuilder
{
    public function build(array $template, array $context): array
    {
        $schema = $this->replacePlaceholders($template, $context);

        return [
            '@context' => 'https://schema.org',
            ...$schema,
        ];
    }

    protected function replacePlaceholders(mixed $value, array $context): mixed
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->replacePlaceholders($item, $context);
            }

            return $result;
        }

        if (! is_string($value)) {
            return $value;
        }

        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function (array $matches) use ($context) {
            $key = $matches[1];

            return match ($key) {
                'app_url' => rtrim((string) config('app.url'), '/'),
                'site_name' => (string) SeoSettings::get('defaults.site_name', config('seo.defaults.site_name')),
                'currency' => function_exists('fn_get_setting')
                    ? (string) fn_get_setting('general.currency', 'USD')
                    : 'USD',
                default => (string) ($context[$key] ?? ''),
            };
        }, $value);
    }

    public function contextFromModel(Model $model, array $meta): array
    {
        $context = [
            'title' => $meta['title'] ?? '',
            'description' => $meta['description'] ?? '',
            'image_url' => $meta['image'] ?? '',
            'created_at' => $model->created_at?->toIso8601String() ?? '',
            'published_at' => ($model->published_at ?? $model->created_at)?->toIso8601String() ?? '',
            'updated_at' => $model->updated_at?->toIso8601String() ?? '',
        ];

        if (isset($model->price)) {
            $context['price'] = (string) $model->price;
        }

        if (method_exists($model, 'getAuthorDisplayNameAttribute') || isset($model->author_name) || $model->relationLoaded('author')) {
            $context['author_name'] = method_exists($model, 'getAuthorDisplayNameAttribute')
                ? (string) $model->author_display_name
                : (string) ($model->author?->name ?? $model->author_name ?? '');
        }

        return $context;
    }
}
