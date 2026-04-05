<?php

use App\Models\Client;
use App\Models\Position;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a team can have multiple positions', function () {
    $team = Team::factory()->create();
    Position::factory()->count(2)->create(['team_id' => $team->id]);

    expect($team->positions)->toHaveCount(2);
    expect($team->positions->first())->toBeInstanceOf(Position::class);
});

test('a team can have multiple clients', function () {
    $team = Team::factory()->create();
    Client::factory()->count(3)->create(['team_id' => $team->id]);

    expect($team->clients)->toHaveCount(3);
    expect($team->clients->first())->toBeInstanceOf(Client::class);
});
