<?php

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a user can start a time entry', function () {
    $user = User::factory()->create();
    $task = Task::factory()->create();

    $timeEntry = TimeEntry::create([
        'user_id' => $user->id,
        'task_id' => $task->id,
        'started_at' => Carbon::now(),
        'is_running' => true,
    ]);

    expect($timeEntry->is_running)->toBeTrue();
    expect($user->activeTimeEntry->id)->toBe($timeEntry->id);
});

test('a user can stop a running time entry', function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 4, 10, 0, 0));
    $user = User::factory()->create();
    $task = Task::factory()->create();

    $timeEntry = TimeEntry::create([
        'user_id' => $user->id,
        'task_id' => $task->id,
        'started_at' => Carbon::now(),
        'is_running' => true,
    ]);

    Carbon::setTestNow(Carbon::now()->addHours(2));

    $timeEntry->stop();

    expect($timeEntry->is_running)->toBeFalse();
    expect($timeEntry->stopped_at->toDateTimeString())->toBe('2026-04-04 12:00:00');
    expect($timeEntry->duration_seconds)->toBe(7200);
});
