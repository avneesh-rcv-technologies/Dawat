<?php

namespace Modules\Seo\Support;

/** Validate document structure locally without fetching remote contexts or vocabularies. */
class JsonLd
{
    public static function valid(mixed $schema): bool
    {
        if (! is_array($schema) || $schema === []) {
            return false;
        }

        if (array_is_list($schema)) {
            foreach ($schema as $node) {
                if (! is_array($node) || array_is_list($node) || ! self::valid($node)) {
                    return false;
                }
            }

            return true;
        }

        if (array_key_exists('@context', $schema) && ! is_string($schema['@context']) && ! is_array($schema['@context'])) {
            return false;
        }

        if (array_key_exists('@type', $schema)) {
            $types = is_array($schema['@type']) ? $schema['@type'] : [$schema['@type']];
            if ($types === [] || ! array_is_list($types)) {
                return false;
            }
            foreach ($types as $type) {
                if (! is_string($type) || trim($type) === '') {
                    return false;
                }
            }
        }

        if (array_key_exists('@id', $schema) && (! is_string($schema['@id']) || trim($schema['@id']) === '')) {
            return false;
        }

        if (array_key_exists('@graph', $schema) && ! self::valid($schema['@graph'])) {
            return false;
        }

        return isset($schema['@type']) || isset($schema['@id']) || isset($schema['@graph']);
    }
}
