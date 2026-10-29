<?php

namespace Modules\EmailNotification\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'content',
        'locale',
        'status'
    ];

    protected $table = 'email_templates';

    protected $casts = [
        'status' => 'boolean'
    ];

    /**
     * Default variables that will be available in all templates
     */
    protected function getDefaultVariables(): array
    {

        $logo = fn_get_image('company_logo', 0)['url'] ?? '';

        return [
            'company_logo' => [
                'value' => $logo,
                'description' => 'URL of the company logo'
            ],
            'company_name' => [
                'value' => fn_get_setting('general.company.name'),
                'description' => 'Name of the company'
            ],
            'company_signature' => [
                'value' => fn_get_setting('general.company.signature') ?? 'Best regards,<br>Your Company',
                'description' => 'Company signature block'
            ],
            'current_year' => [
                'value' => date('Y'),
                'description' => 'Current year'
            ],
            'header' => [
                'value' => fn_get_setting('mail.header')['html'] ?? '',
                'description' => 'Global email header HTML'
            ],
            'footer' => [
                'value' => fn_get_setting('mail.footer')['html'] ?? '',
                'description' => 'Global email footer HTML'
            ],
        ];
    }

    /**
     * Normalize default variables (important)
     */
    protected function getDefaultVariableValues(): array
    {
        return collect($this->getDefaultVariables())
            ->mapWithKeys(function ($item, $key) {
                return [$key => $item['value']];
            })
            ->toArray();
    }

    /**
     * Compile content with variables
     */
    public function compile(array $data): array
    {
        // Merge default + custom data
        $allVariables = array_merge(
            $this->getDefaultVariableValues(),
            $data
        );


        return [
            'subject' => $this->replaceVariables($this->subject, $allVariables),
            'content' => $this->replaceVariables($this->content, $allVariables)
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
