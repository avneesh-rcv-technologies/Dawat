<?php

namespace Modules\Website\Support;

use Modules\Blogs\Services\BlogCtaService;

class BlogContent
{
    /** Separate the optional editor introduction from the article body without changing saved content. */
    public static function split(?string $html): array
    {
        $html = BlogCtaService::resolveShortcodes($html ?? '');
        if (! str_contains($html, 'blog-introduction')) {
            return ['introduction' => '', 'body' => $html];
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
            $body = $document->getElementsByTagName('body')->item(0);
            $intro = (new \DOMXPath($document))->query('/html/body/div[contains(concat(" ", normalize-space(@class), " "), " blog-introduction ")]')->item(0);
            if (! $intro || ! $body) {
                return ['introduction' => '', 'body' => $html];
            }
            $introduction = $document->saveHTML($intro);
            $intro->parentNode->removeChild($intro);
            $content = '';
            foreach ($body->childNodes as $child) {
                $content .= $document->saveHTML($child);
            }

            return ['introduction' => $introduction, 'body' => $content];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
