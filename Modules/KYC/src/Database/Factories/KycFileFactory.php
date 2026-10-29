<?php

namespace Modules\KYC\Database\Factories;

use Modules\KYC\Models\KycFile;
use Modules\KYC\Models\KycSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class KycFileFactory extends Factory
{
    protected $model = KycFile::class;

    public function definition()
    {
        return [
            'submission_id' => KycSubmission::factory(),
            'field_id' => $this->faker->numberBetween(1, 10),
            'file_path' => 'kyc/' . $this->faker->uuid . '.pdf',
            'file_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(1024, 1048576),
            'original_name' => $this->faker->word . '.pdf',
            'uploaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}