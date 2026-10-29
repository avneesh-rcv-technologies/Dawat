<?php

namespace Modules\WebPushNotification\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $table = 'webpush_notification_templates';
    protected $fillable = [
        'name',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Compile the template with the given variables.
     */
    public function compile(array $data): array
    {
        // Merge default + custom data
        $allVariables = array_merge(
            $data
        );


        return [
            'subject' => $this->replaceVariables($this->subject, $allVariables),
            'content' => $this->replaceVariables($this->body, $allVariables)
        ];
    }




    protected function replaceVariables(string $text, array $data): string
    {
        // Handle array loops {{#items}}...{{/items}}
        foreach ($data as $key => $value) {
            if (is_array($value) && $this->isAssocArray($value) === false) {

                // Indexed array → loop
                $pattern = "/{{#{$key}}}(.*?){{\/{$key}}}/s";
                if (preg_match($pattern, $text, $matches)) {
                    $block = $matches[1];
                    $rendered = '';

                    foreach ($value as $row) {
                        $rowBlock = $block;
                        if (is_array($row)) {
                            // Recursively replace inside the row
                            $rowBlock = $this->replaceVariables($rowBlock, $row);
                        } else {
                            // Handle scalar row (string, int, etc.) with {{.}}
                            $rowBlock = Str::replace("{{.}}", (string) $row, $rowBlock);
                        }

                        $rendered .= $rowBlock;
                    }

                    $text = preg_replace($pattern, $rendered, $text);
                }
            }
        }

        // Flatten associative arrays (billing_address.name → "123 Main St")
        $flatData = $this->dotFlatten($data);

        // Replace safe scalars only
        foreach ($flatData as $key => $value) {
            if (is_scalar($value)) {
                $text = Str::replace("{{{$key}}}", (string) $value, $text);
            }
        }

        return $text;
    }


    /**
     * Flatten nested arrays into dot notation
     */
    protected function dotFlatten($data, string $prefix = ''): array
    {
        $results = [];

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
                $results = array_merge($results, $this->dotFlatten($value, $newKey));
            }
        } elseif (is_object($data)) {
            // Convert object → array
            if (method_exists($data, 'toArray')) {
                $arrayData = $data->toArray(); // Eloquent model
            } elseif ($data instanceof \JsonSerializable) {
                $arrayData = (array) $data->jsonSerialize();
            } else {
                $arrayData = get_object_vars($data);
            }

            foreach ($arrayData as $key => $value) {
                $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
                $results = array_merge($results, $this->dotFlatten($value, $newKey));
            }
        } else {
            // scalar
            $results[$prefix] = $data;
        }

        return $results;
    }


    /**
     * Check if array is associative
     */
    protected function isAssocArray(array $array): bool
    {
        if (empty($array)) {
            return false; // empty array is not associative
        }

        // If all keys are integers, treat as indexed
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Get all available variables with descriptions (custom + default)
     */
    public function getAvailableVariables(): array
    {
        $defaultVars = $this->getDefaultVariables();
        $customVars = collect($this->variables ?? [])
            ->mapWithKeys(fn($item) => [$item['name'] => $item['description']])
            ->toArray();

        return array_merge($defaultVars, $customVars);
    }
}
