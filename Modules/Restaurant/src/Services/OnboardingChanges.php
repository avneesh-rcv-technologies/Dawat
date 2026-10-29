<?php

namespace Modules\Restaurant\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycType;

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

    public static function documentChanged(KycType $type, KycSubmission $saved, array $input): bool
    {
        $values = $saved->data->pluck('field_value', 'field_key');
        foreach ($type->fields as $field) {
            $value = $input[$field->field_key] ?? null;
            if ($field->field_type === 'file') {
                if (!$value instanceof UploadedFile) continue;
                $file = $saved->files->firstWhere('field_id', $field->id);
                if (!$file || !Storage::disk('public')->exists($file->file_path)) return true;
                $stream = Storage::disk('public')->readStream($file->file_path);
                if (!is_resource($stream)) return true;
                try {
                    $hash = hash_init('sha256');
                    hash_update_stream($hash, $stream);
                    if (!hash_equals(hash_final($hash), hash_file('sha256', $value->getRealPath()))) return true;
                } finally {
                    fclose($stream);
                }
            } else {
                $previous = $values->get($field->field_key);
                if ($field->field_type === 'number' && is_numeric($value) && is_numeric($previous)) {
                    if ($value != $previous) return true;
                } elseif (trim((string) ($value ?? '')) !== trim((string) ($previous ?? ''))) {
                    return true;
                }
            }
        }
        return false;
    }
}
