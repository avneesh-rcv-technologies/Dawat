<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class OnboardingChanges
{
    public const MESSAGE = 'No changes were made. Please update at least one field before submitting again.';

    public static function reject(string $field): never
    {
        throw ValidationException::withMessages([$field => [self::MESSAGE]]);
    }

    public static function changedValues(Model $record, array $input): array
    {
        return array_filter($input, fn ($value, $field) =>
            self::normalize($field, $value) !== self::normalize($field, $record->getAttribute($field)), ARRAY_FILTER_USE_BOTH);
    }

    private static function normalize(string $field, mixed $value): string
    {
        if (in_array($field, ['same_mailing_address', 'receive_1099_electronically', 'backup_tax_withholding', 'tax_certification'], true)) {
            return (bool) $value ? '1' : '0';
        }
        $value = trim((string) ($value ?? ''));
        return in_array($field, ['ssn', 'ein'], true) ? str_replace('-', '', $value) : $value;
    }

}
