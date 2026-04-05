<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shares_active_time_entry_with_task()
    {
        $user = User::factory()->create();
        $company = $user->currentCompany;

        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['client_id' => $client->id]);
        $task = Task::factory()->create(['project_id' => $project->id]);

        $timeEntry = TimeEntry::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'started_at' => now(),
            'is_running' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard', ['current_company' => $company->slug]))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->has('auth.user.active_time_entry', fn (Assert $page) => $page
                    ->where('id', $timeEntry->id)
                    ->has('task')
                    ->etc()
                )
            );
    }

    public function test_it_handles_no_active_time_entry()
    {
        $user = User::factory()->create();
        $company = $user->currentCompany;

        $this->actingAs($user)
            ->get(route('dashboard', ['current_company' => $company->slug]))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.active_time_entry', null)
            );
    }

    public function test_it_handles_guest_user()
    {
        $this->get(route('home'))
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user', null)
            );
    }
}
