<?php

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\User;

test('company member roles can be updated by owners', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->patch(route('companies.members.update', [$company, $member]), [
            'role' => CompanyRole::Admin->value,
        ]);

    $response->assertRedirect(route('companies.edit', $company));

    expect($company->members()->where('user_id', $member->id)->first()->pivot->role->value)->toEqual(CompanyRole::Admin->value);
});

test('company member roles cannot be updated by non owners', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($admin, ['role' => CompanyRole::Admin->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('companies.members.update', [$company, $member]), [
            'role' => CompanyRole::Admin->value,
        ]);

    $response->assertForbidden();
});

test('company members can be removed by owners', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('companies.members.destroy', [$company, $member]));

    $response->assertRedirect(route('companies.edit', $company));

    expect($member->fresh()->belongsToCompany($company))->toBeFalse();
});

test('company members cannot be removed by non owners', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $member = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($admin, ['role' => CompanyRole::Admin->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $response = $this
        ->actingAs($admin)
        ->delete(route('companies.members.destroy', [$company, $member]));

    $response->assertForbidden();
});

test('company owner cannot be removed', function () {
    $owner = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('companies.members.destroy', [$company, $owner]));

    $response->assertForbidden();

    expect($owner->fresh()->belongsToCompany($company))->toBeTrue();
});

test('company member role cannot be set to owner', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->patch(route('companies.members.update', [$company, $member]), [
            'role' => CompanyRole::Owner->value,
        ]);

    $response->assertSessionHasErrors('role');

    expect($company->members()->where('user_id', $member->id)->first()->pivot->role->value)->toEqual(CompanyRole::Member->value);
});

test('removed member current company is set to personal company', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $personalCompany = $member->personalCompany();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $member->update(['current_company_id' => $company->id]);

    $this
        ->actingAs($owner)
        ->delete(route('companies.members.destroy', [$company, $member]));

    expect($member->fresh()->current_company_id)->toEqual($personalCompany->id);
});
