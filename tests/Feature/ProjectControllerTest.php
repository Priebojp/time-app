<?php

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can show a project', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();
    $client = Client::factory()->for($team)->create();
    $project = Project::factory()->for($client)->create();

    $response = $this->actingAs($user)
        ->get(route('projects.show', [
            'current_team' => $team->slug,
            'project' => $project->id,
        ]));

    $response->assertStatus(200);
});

test('it can index projects', function () {
    $user = User::factory()->create();
    $team = $user->personalTeam();

    $response = $this->actingAs($user)
        ->get(route('projects.index', [
            'current_team' => $team->slug,
        ]));

    $response->assertStatus(200);
});
