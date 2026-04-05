<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a company can have multiple positions', function () {
    $company = Company::factory()->create();
    Position::factory()->count(2)->create(['company_id' => $company->id]);

    expect($company->positions)->toHaveCount(2);
    expect($company->positions->first())->toBeInstanceOf(Position::class);
});

test('a company can have multiple clients', function () {
    $company = Company::factory()->create();
    Client::factory()->count(3)->create(['company_id' => $company->id]);

    expect($company->clients)->toHaveCount(3);
    expect($company->clients->first())->toBeInstanceOf(Client::class);
});
