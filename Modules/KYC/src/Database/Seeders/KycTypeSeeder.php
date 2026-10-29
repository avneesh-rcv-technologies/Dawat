<?php

namespace Modules\KYC\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\KYC\Models\KycField;
use Modules\KYC\Models\KycType;

class KycTypeSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('admins')
            ->join('roles', 'admins.role_id', '=', 'roles.id')
            ->where('roles.guard', 'admin')
            ->where('roles.permission_type', 'all')
            ->orderBy('admins.id')
            ->value('admins.id');
        if ($adminId === null) {
            throw new \RuntimeException('An admin user with an admin-guard role and all permissions must exist before seeding KYC types.');
        }

        $types = [
            [
                'name' => 'Business Registration',
                'description' => 'Articles of incorporation, certificate of formation, or similar business registration document used to verify legal business existence in the U.S.',
                'fields' => [
                    ['field_name' => 'Registration Document', 'field_key' => 'registration_document', 'field_type' => 'file', 'is_required' => true, 'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 10240]],
                ],
            ],
            [
                'name' => 'Food License',
                'description' => 'Local health department food service permit or health inspection permit required for food operations in the U.S.',
                'fields' => [
                    ['field_name' => 'Permit Document', 'field_key' => 'permit_document', 'field_type' => 'file', 'is_required' => true, 'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 10240]],
                ],
            ],
            [
                'name' => 'Tax Certificate',
                'description' => 'Tax registration certificate used to verify the restaurant business tax information.',
                'fields' => [
                    ['field_name' => 'Tax Certificate', 'field_key' => 'tax_certificate', 'field_type' => 'file', 'is_required' => true, 'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 10240]],
                ],
            ],
            [
                'name' => 'Proof of Address',
                'description' => 'Utility bill, lease agreement, or similar document verifying the restaurant business address.',
                'fields' => [
                    ['field_name' => 'Proof of Address Document', 'field_key' => 'proof_of_address_document', 'field_type' => 'file', 'is_required' => true, 'validation_rules' => ['allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'], 'max_size_kb' => 10240]],
                ],
            ],
        ];

        DB::transaction(function () use ($adminId, $types) {
            // Related fields, submissions, and requests are deleted by foreign-key cascades.
            KycType::query()->delete();

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

                $this->command?->info("Seeded KYC type: {$kycType->name}");
            }
        });
    }
}
