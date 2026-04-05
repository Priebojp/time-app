<?php

namespace App\Http\Controllers\Companies;

use App\Enums\CompanyRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Companies\UpdateCompanyMemberRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CompanyMemberController extends Controller
{
    /**
     * Update the specified company member's role.
     */
    public function update(UpdateCompanyMemberRequest $request, Company $company, User $user): RedirectResponse
    {
        Gate::authorize('updateMember', $company);

        $newRole = CompanyRole::from($request->validated('role'));

        $company->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->update([
                'role' => $newRole,
                'status' => 'approved',
            ]);

        return to_route('companies.edit', ['company' => $company->slug]);
    }

    /**
     * Approve the specified company member's join request.
     */
    public function approve(Company $company, User $user): RedirectResponse
    {
        Gate::authorize('approveMember', $company);

        $company->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->update(['status' => 'approved']);

        return to_route('companies.edit', ['company' => $company->slug]);
    }

    /**
     * Remove the specified company member.
     */
    public function destroy(Company $company, User $user): RedirectResponse
    {
        Gate::authorize('removeMember', $company);

        abort_if($company->owner()?->is($user), 403, 'The company owner cannot be removed.');

        $company->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($user->isCurrentCompany($company)) {
            $user->switchCompany($user->personalCompany());
        }

        return to_route('companies.edit', ['company' => $company->slug]);
    }
}
