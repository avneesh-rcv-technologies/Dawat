<?php

namespace Modules\KYC\Database\Factories;

use Modules\KYC\Models\KycRequest;
use Modules\KYC\Models\KycType;
use Illuminate\Database\Eloquent\Factories\Factory;

class KycRequestFactory extends Factory
{
    protected $model = KycRequest::class;

    public function definition()
    {
        return [
            'kyc_type_id' => KycType::factory(),
            'requested_by' => 1,
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}