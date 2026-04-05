<?php

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('company invitations can be created', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);

    $response = $this
        ->actingAs($owner)
        ->post(route('companies.invitations.store', $company), [
            'email' => 'invited@example.com',
            'role' => CompanyRole::Member->value,
        ]);

    $response->assertRedirect(route('companies.edit', $company));

    $this->assertDatabaseHas('company_invitations', [
        'company_id' => $company->id,
        'email' => 'invited@example.com',
        'role' => CompanyRole::Member->value,
    ]);
});

test('company invitations can be created by admins', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($admin, ['role' => CompanyRole::Admin->value]);

    $response = $this
        ->actingAs($admin)
        ->post(route('companies.invitations.store', $company), [
            'email' => 'invited@example.com',
            'role' => CompanyRole::Member->value,
        ]);

    $response->assertRedirect(route('companies.edit', $company));
});

test('existing company members cannot be invited', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $member = User::factory()->create(['email' => 'member@example.com']);
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $response = $this
        ->actingAs($owner)
        ->post(route('companies.invitations.store', $company), [
            'email' => 'member@example.com',
            'role' => CompanyRole::Member->value,
        ]);

    $response->assertSessionHasErrors('email');
});

test('duplicate invitations cannot be created', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $company = Company::factory()->create();
    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);

    CompanyInvitation::factory()->create([
        'company_id' => $company->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($owner)
        ->post(route('companies.invitations.store', $company), [
            'email' => 'invited@example.com',
            'role' => CompanyRole::Member->value,
        ]);

    $response->assertSessionHasErrors('email');
});

test('company invitations cannot be created by members', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);
    $company->members()->attach($member, ['role' => CompanyRole::Member->value]);

    $response = $this
        ->actingAs($member)
        ->post(route('companies.invitations.store', $company), [
            'email' => 'invited@example.com',
            'role' => CompanyRole::Member->value,
        ]);

    $response->assertForbidden();
});

test('company invitations can be cancelled by owners', function () {
    $owner = User::factory()->create();
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);

    $invitation = CompanyInvitation::factory()->create([
        'company_id' => $company->id,
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($owner)
        ->delete(route('companies.invitations.destroy', [$company, $invitation]));

    $response->assertRedirect(route('companies.edit', $company));

    $this->assertDatabaseMissing('company_invitations', [
        'id' => $invitation->id,
    ]);
});

test('company invitations can be accepted', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);

    $invitation = CompanyInvitation::factory()->create([
        'company_id' => $company->id,
        'email' => 'invited@example.com',
        'role' => CompanyRole::Member,
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertRedirect(route('dashboard'));

    expect($invitedUser->fresh()->belongsToCompany($company))->toBeTrue();
    expect($invitation->fresh()->accepted_at)->not->toBeNull();
});

test('company invitations cannot be accepted by uninvited user', function () {
    $owner = User::factory()->create();
    $uninvitedUser = User::factory()->create(['email' => 'uninvited@example.com']);
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);

    $invitation = CompanyInvitation::factory()->create([
        'company_id' => $company->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($uninvitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertSessionHasErrors('invitation');

    expect($uninvitedUser->fresh()->belongsToCompany($company))->toBeFalse();
});

test('expired invitations cannot be accepted', function () {
    $owner = User::factory()->create();
    $invitedUser = User::factory()->create(['email' => 'invited@example.com']);
    $company = Company::factory()->create();

    $company->members()->attach($owner, ['role' => CompanyRole::Owner->value]);

    $invitation = CompanyInvitation::factory()->expired()->create([
        'company_id' => $company->id,
        'email' => 'invited@example.com',
        'invited_by' => $owner->id,
    ]);

    $response = $this
        ->actingAs($invitedUser)
        ->get(route('invitations.accept', $invitation));

    $response->assertSessionHasErrors('invitation');

    expect($invitedUser->fresh()->belongsToCompany($company))->toBeFalse();
});
