<?php

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Session;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user->companies)->toHaveCount(1);
    expect($user->personalCompany())->not->toBeNull();

    $response->assertRedirect(route('dashboard'));
});

test('new users can register with an invitation', function () {
    $company = Company::factory()->create(['name' => 'Existing Company']);
    $invitation = CompanyInvitation::factory()->create([
        'company_id' => $company->id,
        'email' => 'invited@example.com',
        'role' => CompanyRole::Member,
    ]);

    // Scenario 1: Invitation by email
    $response = $this->post(route('register'), [
        'name' => 'Invited User',
        'email' => 'invited@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'invited@example.com')->first();
    expect($user->companies)->toHaveCount(1);
    expect($user->personalCompany())->toBeNull();
    expect($user->belongsToCompany($company))->toBeTrue();
    expect($user->currentCompany->id)->toBe($company->id);

    $invitation->refresh();
    expect($invitation->isAccepted())->toBeTrue();

    $response->assertRedirect(route('dashboard'));
});

test('new users can register with an invitation token in session', function () {
    $company = Company::factory()->create(['name' => 'Token Company']);
    $invitation = CompanyInvitation::factory()->create([
        'company_id' => $company->id,
        'email' => 'other@example.com',
        'role' => CompanyRole::Member,
    ]);

    // Simulate clicking the invitation link which sets the session
    Session::put('company_invitation', $invitation->code);

    $response = $this->post(route('register'), [
        'name' => 'Token User',
        'email' => 'token@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'token@example.com')->first();
    expect($user->companies)->toHaveCount(1);
    expect($user->personalCompany())->toBeNull();
    expect($user->belongsToCompany($company))->toBeTrue();
    expect($user->currentCompany->id)->toBe($company->id);

    $invitation->refresh();
    expect($invitation->isAccepted())->toBeTrue();

    $response->assertRedirect(route('dashboard'));
});
