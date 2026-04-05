<?php

namespace App\Actions\Fortify;

use App\Actions\Companies\CreateCompany;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use App\Notifications\Teams\CompanyJoinRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private CreateCompany $createCompany)
    {
        //
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $invitation = $this->getInvitation($user->email);

            if ($invitation) {
                $this->joinCompany($user, $invitation);
            } else {
                $matchingCompany = $this->findMatchingCompany($user->email);

                if ($matchingCompany) {
                    $this->requestToJoinCompany($user, $matchingCompany);
                } else {
                    $this->createCompany->handle($user, $user->name."'s Company", isPersonal: true);
                }
            }

            return $user;
        });
    }

    /**
     * Find a company that matches the user's email domain.
     */
    protected function findMatchingCompany(string $email): ?Company
    {
        $domain = substr(strrchr($email, '@'), 1);

        if (! $domain) {
            return null;
        }

        return Company::whereJsonContains('allowed_domains', $domain)->first();
    }

    /**
     * Request to join a company via domain matching.
     */
    protected function requestToJoinCompany(User $user, Company $company): void
    {
        $company->memberships()->create([
            'user_id' => $user->id,
            'role' => CompanyRole::Member->value,
            'status' => 'pending',
        ]);

        $user->switchCompany($company);

        // Notify company owner/admins
        $company->members()
            ->wherePivotIn('role', [CompanyRole::Owner->value, CompanyRole::Admin->value])
            ->get()
            ->each(fn (User $admin) => $admin->notify(new CompanyJoinRequest($company, $user)));
    }

    /**
     * Get the invitation for the user.
     */
    protected function getInvitation(string $email): ?CompanyInvitation
    {
        $code = Session::get('company_invitation');

        if ($code) {
            $invitation = CompanyInvitation::where('code', $code)->first();

            if ($invitation && $invitation->isPending()) {
                return $invitation;
            }
        }

        return CompanyInvitation::where('email', $email)
            ->whereNull('accepted_at')
            ->first();
    }

    /**
     * Join the user to the company.
     */
    protected function joinCompany(User $user, CompanyInvitation $invitation): void
    {
        $company = $invitation->company;

        $company->memberships()->create([
            'user_id' => $user->id,
            'role' => $invitation->role,
        ]);

        $invitation->update(['accepted_at' => now()]);

        $user->switchCompany($company);

        Session::forget('company_invitation');
    }
}
