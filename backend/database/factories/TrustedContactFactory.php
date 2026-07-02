<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrustedContactFactory extends Factory
{
    protected $model = \App\Models\TrustedContact::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'phone' => '+234' . $this->faker->numerify('##########'),
            'email' => $this->faker->email(),
            'verified' => false,
            'verification_token' => null,
            'created_at' => now(),
        ];
    }
}
