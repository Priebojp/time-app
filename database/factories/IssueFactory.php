<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'reporter_id' => User::factory(),
            'assignee_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'review', 'done']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'order_index' => $this->faker->numberBetween(0, 100),
        ];
    }
}
