<?php

namespace Modules\KYC\Services;

use Modules\KYC\Models\KycType;
use Modules\KYC\Models\KycField;
use Modules\KYC\Exceptions\KycException;
use Modules\KYC\Traits\HandlesKycErrors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class KycTypeService
{
    use HandlesKycErrors;
    /**
     * Get all KYC types with fields
     */
    public function getAllTypes(bool $activeOnly = false): Collection
    {
        $query = KycType::with(['fields', 'creator']);
        
        if ($activeOnly) {
            $query->active();
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a single KYC type with fields
     */
    public function getType(int $id): KycType
    {
        try {
            $this->logKycOperation('Getting KYC Type', ['type_id' => $id]);
            
            $kycType = KycType::with(['fields', 'creator'])->find($id);
            
            if (!$kycType) {
                throw KycException::typeNotFound($id);
            }
            
            return $kycType;
        } catch (Exception $e) {
            Log::error('Failed to get KYC Type', [
                'type_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a new KYC type
     */
    public function createType(array $data, int $adminId): KycType
    {
        try {
            $this->validateKycParameters(['name', 'fields'], $data);
            
            // Validate that fields array is not empty
            if (empty($data['fields']) || !is_array($data['fields']) || count($data['fields']) === 0) {
                throw new KycException(
                    'At least one field must be configured for the KYC Type',
                    'FIELDS_REQUIRED',
                    422,
                    ['provided_fields_count' => count($data['fields'] ?? [])]
                );
            }

            $this->logKycOperation('Creating KYC Type', [
                'name' => $data['name'],
                'admin_id' => $adminId,
                'fields_count' => count($data['fields'])
            ]);

            return DB::transaction(function () use ($data, $adminId) {
                // Check for duplicate name
                if (KycType::where('name', $data['name'])->exists()) {
                    throw new KycException(
                        'KYC Type with this name already exists',
                        'DUPLICATE_NAME',
                        422,
                        ['name' => $data['name']]
                    );
                }

                $kycType = KycType::create([
                    'name' => $data['name'],
                    'type' => $data['type'] ?? 'kyc',
                    'description' => $data['description'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                    'created_by' => $adminId
                ]);

                // Validate and create fields
                $this->validateAndCreateFields($kycType, $data['fields']);

                return $kycType->load('fields');
            });
        } catch (Exception $e) {
            Log::error('Failed to create KYC Type', [
                'data' => $data,
                'admin_id' => $adminId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update a KYC type
     */
    public function updateType(int $id, array $data): KycType
    {
        return DB::transaction(function () use ($id, $data) {
            $kycType = KycType::findOrFail($id);
            
            $kycType->update([
                'name' => $data['name'] ?? $kycType->name,
                'type' => $data['type'] ?? $kycType->type,
                'description' => $data['description'] ?? $kycType->description,
                'is_active' => $data['is_active'] ?? $kycType->is_active
            ]);

            return $kycType->fresh('fields');
        });
    }

    /**
     * Delete a KYC type
     */
    public function deleteType(int $id): bool
    {
        try {
            $this->logKycOperation('Deleting KYC Type', ['type_id' => $id]);
            
            $kycType = KycType::find($id);
            if (!$kycType) {
                throw KycException::typeNotFound($id);
            }
            
            if ($kycType->isInUse()) {
                throw new KycException(
                    'Cannot delete KYC type that is currently in use',
                    'TYPE_IN_USE',
                    422,
                    [
                        'type_id' => $id,
                        'submissions_count' => $kycType->submissions()->count(),
                        'requests_count' => $kycType->requests()->count()
                    ]
                );
            }
            
            return DB::transaction(function () use ($kycType) {
                // Delete associated fields first
                $kycType->fields()->delete();
                
                // Delete the KYC type
                return $kycType->delete();
            });
        } catch (Exception $e) {
            Log::error('Failed to delete KYC Type', [
                'type_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate and create fields for a KYC type
     */
    protected function validateAndCreateFields(KycType $kycType, array $fields): void
    {
        // Validate field keys are unique
        $fieldKeys = array_column($fields, 'field_key');
        if (count($fieldKeys) !== count(array_unique($fieldKeys))) {
            throw new KycException(
                'Field keys must be unique within the same KYC Type',
                'DUPLICATE_FIELD_KEYS',
                422,
                ['duplicate_keys' => array_diff_assoc($fieldKeys, array_unique($fieldKeys))]
            );
        }

        foreach ($fields as $index => $field) {
            // Validate required field data
            $requiredFields = ['field_name', 'field_key', 'field_type'];
            foreach ($requiredFields as $requiredField) {
                if (empty($field[$requiredField])) {
                    throw new KycException(
                        "Field {$requiredField} is required for field at position " . ($index + 1),
                        'MISSING_FIELD_DATA',
                        422,
                        ['field_index' => $index, 'missing_field' => $requiredField]
                    );
                }
            }

            // Validate field key format
            if (!preg_match('/^[a-z0-9_]+$/', $field['field_key'])) {
                throw new KycException(
                    "Field key '{$field['field_key']}' must contain only lowercase letters, numbers, and underscores",
                    'INVALID_FIELD_KEY_FORMAT',
                    422,
                    ['field_key' => $field['field_key'], 'field_index' => $index]
                );
            }

            // Validate select field has options
            if ($field['field_type'] === 'select') {
                if (empty($field['options']) || !is_array($field['options']) || count($field['options']) === 0) {
                    throw new KycException(
                        "Select field '{$field['field_name']}' must have at least one option",
                        'SELECT_FIELD_NO_OPTIONS',
                        422,
                        ['field_name' => $field['field_name'], 'field_index' => $index]
                    );
                }
            }

            KycField::create([
                'kyc_type_id'      => $kycType->id,
                'field_name'       => $field['field_name'],
                'field_key'        => $field['field_key'],
                'field_type'       => $field['field_type'],
                'is_required'      => $field['is_required'] ?? true,
                'options'          => $field['options'] ?? null,
                'order_index'      => $field['order_index'] ?? $index,
                'validation_rules' => $field['validation_rules'] ?? null,
            ]);
        }
    }

    /**
     * Create fields for a KYC type (legacy method for backward compatibility)
     */
    public function createFields(KycType $kycType, array $fields): void
    {
        $this->validateAndCreateFields($kycType, $fields);
    }

    /**
     * Add a field to a KYC type
     */
    public function addField(int $kycTypeId, array $fieldData): KycField
    {
        $kycType = KycType::findOrFail($kycTypeId);
        
        // Get the max order index
        $maxOrder = $kycType->fields()->max('order_index') ?? -1;
        
        return KycField::create([
            'kyc_type_id'      => $kycTypeId,
            'field_name'       => $fieldData['field_name'],
            'field_key'        => $fieldData['field_key'],
            'field_type'       => $fieldData['field_type'],
            'is_required'      => $fieldData['is_required'] ?? true,
            'options'          => $fieldData['options'] ?? null,
            'order_index'      => $fieldData['order_index'] ?? ($maxOrder + 1),
            'validation_rules' => $fieldData['validation_rules'] ?? null,
        ]);
    }

    /**
     * Update a field
     */
    public function updateField(int $fieldId, array $data): KycField
    {
        $field = KycField::findOrFail($fieldId);
        
        $field->update([
            'field_name'       => $data['field_name']       ?? $field->field_name,
            'field_key'        => $data['field_key']        ?? $field->field_key,
            'field_type'       => $data['field_type']       ?? $field->field_type,
            'is_required'      => $data['is_required']      ?? $field->is_required,
            'options'          => $data['options']          ?? $field->options,
            'order_index'      => $data['order_index']      ?? $field->order_index,
            'validation_rules' => array_key_exists('validation_rules', $data)
                                    ? $data['validation_rules']
                                    : $field->validation_rules,
        ]);
        
        return $field->fresh();
    }

    /**
     * Delete a field
     */
    public function deleteField(int $fieldId): bool
    {
        $field = KycField::findOrFail($fieldId);
        
        // Check if field has any submission data
        if ($field->submissionData()->exists()) {
            throw new \Exception('Cannot delete field that has submission data');
        }
        
        return $field->delete();
    }

    /**
     * Reorder fields
     */
    public function reorderFields(int $kycTypeId, array $fieldIds): void
    {
        DB::transaction(function () use ($kycTypeId, $fieldIds) {
            foreach ($fieldIds as $index => $fieldId) {
                KycField::where('id', $fieldId)
                    ->where('kyc_type_id', $kycTypeId)
                    ->update(['order_index' => $index]);
            }
        });
    }
}
