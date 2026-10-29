<?php

namespace Modules\KYC\Database\Factories;

use Modules\KYC\Models\KycType;
use Illuminate\Database\Eloquent\Factories\Factory;

class KycTypeFactory extends Factory
{
    protected $model = KycType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}