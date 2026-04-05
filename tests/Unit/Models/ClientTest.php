<?php

use App\Models\Client;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a client belongs to a company', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    expect($client->company)->toBeInstanceOf(Company::class);
    expect($client->company->id)->toBe($company->id);
});
