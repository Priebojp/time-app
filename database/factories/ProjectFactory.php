<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'total_budget' => $this->faker->randomFloat(2, 1000, 50000),
            'monthly_budget' => $this->faker->randomFloat(2, 100, 5000),
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'status' => 'active',
        ];
    }
}
