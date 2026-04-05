<?php

use App\Models\Position;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a position belongs to a team', function () {
    $team = Team::factory()->create();
    $position = Position::factory()->create(['team_id' => $team->id]);

    expect($position->team)->toBeInstanceOf(Team::class);
    expect($position->team->id)->toBe($team->id);
});

test('a position can have multiple users', function () {
    $position = Position::factory()->create();
    $users = User::factory()->count(3)->create();

    $position->users()->attach($users);

    expect($position->users)->toHaveCount(3);
    expect($position->users->first())->toBeInstanceOf(User::class);
});
