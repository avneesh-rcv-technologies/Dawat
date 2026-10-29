<?php

namespace Modules\KYC\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KycField extends Model
{
    protected $fillable = [
        'kyc_type_id',
        'field_name',
        'field_key',
        'field_type',
        'is_required',
        'options',
        'order_index',
        'validation_rules',
    ];

    protected $casts = [
        'is_required'      => 'boolean',
        'options'          => 'array',
        'order_index'      => 'integer',
        'validation_rules' => 'array',
    ];

    public function kycType(): BelongsTo
    {
        return $this->belongsTo(KycType::class, 'kyc_type_id');
    }

    public function submissionData(): HasMany
    {
        return $this->hasMany(KycSubmissionData::class, 'field_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(KycFile::class, 'field_id');
    }

    public function isFileType(): bool
    {
        return $this->field_type === 'file';
    }

    /**
     * Preset format patterns available for text fields.
     * Admin picks a friendly label; this maps to the actual regex.
     */
    public static array $textFormatPresets = [
        'none'               => ['regex' => null,                    'label' => 'Any text'],
        'letters_only'       => ['regex' => '^[A-Za-z ]+$',         'label' => 'Letters only (a-z, A-Z, spaces)'],
        'numbers_only'       => ['regex' => '^[0-9]+$',             'label' => 'Numbers only (0-9)'],
        'alphanumeric'       => ['regex' => '^[A-Za-z0-9 ]+$',      'label' => 'Alphanumeric (letters, numbers, spaces)'],
        'alphanumeric_strict'=> ['regex' => '^[A-Za-z0-9]+$',       'label' => 'Alphanumeric strict (no spaces)'],
        'email'              => ['regex' => null,                    'label' => 'Email format'],
    ];

    /**
     * Build Laravel validation rules from stored validation_rules JSON.
     */
    public function getValidationRules(): array
    {
        $rules   = [];
        $stored  = $this->validation_rules ?? [];

        $rules[] = $this->is_required ? 'required' : 'nullable';

        switch ($this->field_type) {
            case 'text':
                $rules[] = 'string';
                $max = $stored['max_length'] ?? 255;
                $rules[] = "max:{$max}";
                if (!empty($stored['min_length'])) {
                    $rules[] = "min:{$stored['min_length']}";
                }
                // Resolve format preset to actual rule
                $format = $stored['format'] ?? 'none';
                if ($format === 'email') {
                    $rules[] = 'email';
                } elseif ($format !== 'none' && isset(self::$textFormatPresets[$format])) {
                    $regex = self::$textFormatPresets[$format]['regex'];
                    if ($regex) {
                        $rules[] = 'regex:/' . $regex . '/';
                    }
                }
                break;

            case 'number':
                $rules[] = 'numeric';
                if (isset($stored['min']) && $stored['min'] !== '') {
                    $rules[] = "min:{$stored['min']}";
                }
                if (isset($stored['max']) && $stored['max'] !== '') {
                    $rules[] = "max:{$stored['max']}";
                }
                break;

            case 'file':
                $rules[] = 'file';
                $maxKb = $stored['max_size_kb'] ?? 10240;
                $rules[] = "max:{$maxKb}";
                $types = !empty($stored['allowed_types'])
                    ? (is_array($stored['allowed_types'])
                        ? $stored['allowed_types']
                        : explode(',', $stored['allowed_types']))
                    : ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                $rules[] = 'mimes:' . implode(',', array_map('trim', $types));
                break;

            case 'date':
                $rules[] = 'date';
                if (!empty($stored['min_date'])) {
                    $rules[] = "after_or_equal:{$stored['min_date']}";
                }
                if (!empty($stored['max_date'])) {
                    $max = $stored['max_date'] === 'today'
                        ? now()->toDateString()
                        : $stored['max_date'];
                    $rules[] = "before_or_equal:{$max}";
                }
                break;

            case 'select':
                if (!empty($this->options)) {
                    $rules[] = 'in:' . implode(',', array_keys($this->options));
                }
                break;
        }

        return $rules;
    }

    /**
     * Build human-readable validation messages from stored validation_rules JSON.
     */
    public function getValidationMessages(): array
    {
        $messages = [];
        $stored   = $this->validation_rules ?? [];
        $key      = $this->field_key;
        $name     = $this->field_name;

        $messages["{$key}.required"] = "{$name} is required";

        switch ($this->field_type) {
            case 'text':
                $messages["{$key}.string"]  = "{$name} must be valid text";
                $messages["{$key}.max"]     = "{$name} must not exceed " . ($stored['max_length'] ?? 255) . " characters";
                if (!empty($stored['min_length'])) {
                    $messages["{$key}.min"] = "{$name} must be at least {$stored['min_length']} characters";
                }
                // Human-readable message based on format preset
                $format = $stored['format'] ?? 'none';
                if ($format === 'email') {
                    $messages["{$key}.email"] = "{$name} must be a valid email address";
                } elseif ($format !== 'none' && isset(self::$textFormatPresets[$format])) {
                    $label = self::$textFormatPresets[$format]['label'];
                    $messages["{$key}.regex"] = "{$name} format is invalid — allowed: {$label}";
                }
                break;

            case 'number':
                $messages["{$key}.numeric"] = "{$name} must be a number";
                if (isset($stored['min']) && $stored['min'] !== '') {
                    $messages["{$key}.min"] = "{$name} must be at least {$stored['min']}";
                }
                if (isset($stored['max']) && $stored['max'] !== '') {
                    $messages["{$key}.max"] = "{$name} must not exceed {$stored['max']}";
                }
                break;


            case 'file':
                $messages["{$key}.uploaded"] = "{$name} failed to upload. Please check the file size / try again.";
                $messages["{$key}.file"] = "{$name} must be a valid file";

                $maxKb = $stored['max_size_kb'] ?? 10240;
                $messages["{$key}.max"] = "{$name} must not exceed " . round($maxKb / 1024, 1) . " MB";

                $types = !empty($stored['allowed_types'])
                    ? (is_array($stored['allowed_types'])
                        ? $stored['allowed_types']
                        : explode(',', $stored['allowed_types']))
                    : ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];

                $types = array_map('trim', $types);

                $messages["{$key}.mimes"] = "{$name} must be one of: " . implode(', ', $types);

                break;

            case 'date':
                $messages["{$key}.date"] = "{$name} must be a valid date";
                if (!empty($stored['min_date'])) {
                    $messages["{$key}.after_or_equal"] = "{$name} must be on or after {$stored['min_date']}";
                }
                if (!empty($stored['max_date'])) {
                    $messages["{$key}.before_or_equal"] = "{$name} must be on or before {$stored['max_date']}";
                }
                break;

            case 'select':
                $messages["{$key}.in"] = "{$name} has an invalid selection";
                break;
        }

        return $messages;
    }
}
