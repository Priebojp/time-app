<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $is_running = $this->faker->boolean(20);
        $stoppedAt = $is_running ? null : (clone $startedAt)->modify('+'.$this->faker->numberBetween(1800, 28800).' seconds');
        $duration = $stoppedAt ? (clone $startedAt)->diff($stoppedAt)->s + (clone $startedAt)->diff($stoppedAt)->i * 60 + (clone $startedAt)->diff($stoppedAt)->h * 3600 : 0;

        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'started_at' => $startedAt,
            'stopped_at' => $stoppedAt,
            'duration_seconds' => $duration,
            'note' => $this->faker->sentence(),
            'is_running' => $is_running,
        ];
    }
}
