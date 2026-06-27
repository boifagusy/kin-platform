<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckInFactory extends Factory
{
    protected $model = \App\Models\CheckIn::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'checked_in_at' => now(),
            'status' => 'completed',
            'location' => json_encode([
                'lat' => $this->faker->latitude(6, 7),
                'lng' => $this->faker->longitude(3, 4),
            ]),
            'created_at' => now(),
        ];
    }
}
