<?php

use App\Models\Client;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a client belongs to a team', function () {
    $team = Team::factory()->create();
    $client = Client::factory()->create(['team_id' => $team->id]);

    expect($client->team)->toBeInstanceOf(Team::class);
    expect($client->team->id)->toBe($team->id);
});
