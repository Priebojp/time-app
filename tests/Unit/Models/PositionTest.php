<?php

use App\Models\Company;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a position belongs to a company', function () {
    $company = Company::factory()->create();
    $position = Position::factory()->create(['company_id' => $company->id]);

    expect($position->company)->toBeInstanceOf(Company::class);
    expect($position->company->id)->toBe($company->id);
});

test('a position can have multiple users', function () {
    $position = Position::factory()->create();
    $users = User::factory()->count(3)->create();

    $position->users()->attach($users);

    expect($position->users)->toHaveCount(3);
    expect($position->users->first())->toBeInstanceOf(User::class);
});
