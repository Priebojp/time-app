<?php

namespace Database\Factories;

use App\Models\HourlyRate;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HourlyRate>
 */
class HourlyRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
            'rate' => $this->faker->randomFloat(2, 20, 150),
            'currency' => 'USD',
            'valid_from' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'valid_to' => null,
        ];
    }
}
