<?php

namespace Modules\Seo\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Modules\Seo\DTO\SeoPayload;
use Modules\Seo\Services\SeoResolver;

class SeoMeta extends Component
{
    public SeoPayload $payload;

    public function __construct(
        protected SeoResolver $resolver,
        public ?string $key = null,
        public ?string $source = null,
        public ?Model $model = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $keywords = null,
        public ?string $canonical = null,
        public ?string $robots = null,
        public ?string $ogImage = null,
        public ?string $ogType = null,
    ) {
        $overrides = array_filter([
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'robots' => $robots,
            'ogImage' => $ogImage,
            'ogType' => $ogType,
        ], fn ($value) => $value !== null);

        if ($source && $model) {
            $this->payload = $this->resolver->forSource($source, $model, $overrides);
        } elseif ($key) {
            $this->payload = $this->resolver->forKey($key, $overrides);
        } else {
            $this->payload = $this->resolver->forCurrentRequest($overrides);
        }
    }

    public function render(): View
    {
        return view('seo::components.seo-meta');
    }
}
