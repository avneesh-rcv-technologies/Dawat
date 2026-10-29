<?php

namespace App\Traits;

trait SanitizesInput
{
    /**
     * Sanitize an array of string fields.
     * Removes script tags, strips HTML tags, trims and encodes special chars.
     *
     * @param array $fields
     * @return array
     */
    protected function sanitizeStringFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                // Remove any <script>...</script> blocks
                $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);

                // Strip tags and trim
                $value = strip_tags($value);
                $value = trim($value);

                // Encode special characters to be safe for output
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
