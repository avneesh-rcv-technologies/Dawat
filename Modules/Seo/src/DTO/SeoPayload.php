<?php

namespace Modules\Seo\DTO;

class SeoPayload
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $keywords = null,
        public ?string $canonical = null,
        public ?string $robots = null,
        public ?string $ogImage = null,
        public ?string $twitterImage = null,
        public ?string $ogType = null,
        public array $schema = [],
        public array $extraMeta = [],
    ) {}
}
