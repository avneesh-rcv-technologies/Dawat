<?php

namespace Modules\KYC\Database\Factories;

use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycType;
use Illuminate\Database\Eloquent\Factories\Factory;

class KycSubmissionFactory extends Factory
{
    protected $model = KycSubmission::class;

    public function definition()
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 100),
            'kyc_type_id' => KycType::factory(),
            'status' => 'pending',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'reviewed_by' => 1,
                'reviewed_at' => now(),
            ];
        });
    }

    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'reviewed_by' => 1,
                'reviewed_at' => now(),
                'rejection_reason' => $this->faker->sentence(),
            ];
        });
    }
}