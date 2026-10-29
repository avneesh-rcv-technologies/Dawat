<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\KYC\Models\KycField;
use Modules\KYC\Models\KycType;

/**
 * Core identity documents for restaurant owner onboarding: PAN & Aadhaar.
 */
class OwnerIdentityDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('admins')->value('id') ?? 1;

        $types = [
            [
                'name' => 'PAN Card',
                'description' => 'Permanent Account Number (PAN) verification for restaurant owner onboarding.',
                'fields' => [
                    [
                        'field_name' => 'Full Name (as on PAN)',
                        'field_key' => 'full_name',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['format' => 'none', 'max_length' => 255],
                    ],
                    [
                        'field_name' => 'PAN Number',
                        'field_key' => 'pan_number',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['format' => 'alphanumeric_strict', 'max_length' => 10, 'min_length' => 10],
                    ],
                    [
                        'field_name' => 'PAN Card Copy',
                        'field_key' => 'pan_copy',
                        'field_type' => 'file',
                        'is_required' => true,
                        'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 5120],
                    ],
                ],
            ],
            [
                'name' => 'Aadhaar Card',
                'description' => 'Aadhaar identity verification for restaurant owner onboarding.',
                'fields' => [
                    [
                        'field_name' => 'Full Name (as on Aadhaar)',
                        'field_key' => 'full_name',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['format' => 'none', 'max_length' => 255],
                    ],
                    [
                        'field_name' => 'Aadhaar Number',
                        'field_key' => 'aadhaar_number',
                        'field_type' => 'text',
                        'is_required' => true,
                        'validation_rules' => ['format' => 'numbers_only', 'max_length' => 12, 'min_length' => 12],
                    ],
                    [
                        'field_name' => 'Aadhaar Front Copy',
                        'field_key' => 'aadhaar_front',
                        'field_type' => 'file',
                        'is_required' => true,
                        'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 5120],
                    ],
                    [
                        'field_name' => 'Aadhaar Back Copy',
                        'field_key' => 'aadhaar_back',
                        'field_type' => 'file',
                        'is_required' => false,
                        'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 5120],
                    ],
                ],
            ],
        ];

        foreach ($types as $typeData) {
            $kycType = KycType::updateOrCreate(
                ['name' => $typeData['name']],
                [
                    'description' => $typeData['description'],
                    'is_active' => true,
                    'created_by' => $adminId,
                    'type' => 'kyc',
                ]
            );

            foreach ($typeData['fields'] as $index => $fieldData) {
                KycField::updateOrCreate(
                    [
                        'kyc_type_id' => $kycType->id,
                        'field_key' => $fieldData['field_key'],
                    ],
                    [
                        'field_name' => $fieldData['field_name'],
                        'field_type' => $fieldData['field_type'],
                        'is_required' => $fieldData['is_required'],
                        'options' => $fieldData['options'] ?? null,
                        'validation_rules' => $fieldData['validation_rules'] ?? null,
                        'order_index' => $index + 1,
                    ]
                );
            }

            $this->command?->info("Seeded KYC document: {$kycType->name}");
        }
    }
}
