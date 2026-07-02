<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SafetyIncidentFactory extends Factory
{
    protected $model = \App\Models\SafetyIncident::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'type' => 'safety_concern',
            'status' => 'active',
            'is_duress' => false,
            'description' => $this->faker->sentence(),
            'created_at' => now(),
        ];
    }
}
