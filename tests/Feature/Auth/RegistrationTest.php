<?php

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use App\Notifications\Teams\CompanyJoinRequest;
use Illuminate\Support\Facades\Notification;
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

    $response->assertRedirect(route('dashboard', ['current_company' => $user->personalCompany()->slug]));
});

test('new users can register with domain matching', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $company = Company::factory()->create([
        'name' => 'Domain Company',
        'allowed_domains' => ['example.com'],
    ]);
    $company->memberships()->create([
        'user_id' => $owner->id,
        'role' => CompanyRole::Owner->value,
    ]);

    $response = $this->post(route('register'), [
        'name' => 'Domain User',
        'email' => 'user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'user@example.com')->first();

    // Check that NO personal company was created
    expect($user->personalCompany())->toBeNull();

    // Check that user is attached to the domain-matching company
    expect($user->companies)->toHaveCount(1);
    expect($user->companies->first()->id)->toBe($company->id);

    // Check membership status is pending
    $membership = $user->companyMemberships()->where('company_id', $company->id)->first();
    expect($membership->status)->toBe('pending');

    // Check notification was sent to owner
    Notification::assertSentTo($owner, CompanyJoinRequest::class, function ($notification) use ($company, $user) {
        return $notification->company->id === $company->id && $notification->user->id === $user->id;
    });

    $response->assertRedirect(route('dashboard', ['current_company' => $company->slug]));

    $this->get(route('dashboard', ['current_company' => $company->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('status', 'pending_approval')
            ->where('company_name', $company->name)
        );

    // Verify other routes are blocked
    $this->get(route('positions.index', ['current_company' => $company->slug]))
        ->assertForbidden();
});

test('admin can approve domain matching join request', function () {
    $owner = User::factory()->create();
    $company = Company::factory()->create(['name' => 'Company to Join']);
    $company->memberships()->create([
        'user_id' => $owner->id,
        'role' => CompanyRole::Owner->value,
    ]);

    $user = User::factory()->create();
    $company->memberships()->create([
        'user_id' => $user->id,
        'role' => CompanyRole::Member->value,
        'status' => 'pending',
    ]);

    $this->actingAs($owner);

    $response = $this->post(route('companies.members.approve', [$company, $user]));

    $membership = $company->memberships()->where('user_id', $user->id)->first();
    expect($membership->status)->toBe('approved');

    $response->assertRedirect(route('companies.edit', $company));
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
