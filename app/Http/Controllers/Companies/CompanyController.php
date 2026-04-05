<?php

namespace App\Http\Controllers\Companies;

use App\Actions\Companies\CreateCompany;
use App\Enums\CompanyRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Companies\DeleteCompanyRequest;
use App\Http\Requests\Companies\SaveCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the user's companies.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('companies/Index', [
            'companies' => $user->toUserCompanies(includeCurrent: true),
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(SaveCompanyRequest $request, CreateCompany $createCompany): RedirectResponse
    {
        $company = $createCompany->handle($request->user(), $request->validated('name'));

        return to_route('companies.edit', ['company' => $company->slug]);
    }

    /**
     * Show the company edit page.
     */
    public function edit(Request $request, Company $company): Response
    {
        $user = $request->user();

        return Inertia::render('companies/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'isPersonal' => $company->is_personal,
            ],
            'members' => $company->members()->get()->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar ?? null,
                'role' => $member->pivot->role->value,
                'role_label' => $member->pivot->role?->label(),
            ]),
            'invitations' => $company->invitations()
                ->whereNull('accepted_at')
                ->get()
                ->map(fn ($invitation) => [
                    'code' => $invitation->code,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'role_label' => $invitation->role->label(),
                    'created_at' => $invitation->created_at->toISOString(),
                ]),
            'permissions' => $user->toCompanyPermissions($company),
            'availableRoles' => CompanyRole::assignable(),
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(SaveCompanyRequest $request, Company $company): RedirectResponse
    {
        Gate::authorize('update', $company);

        $company = DB::transaction(function () use ($request, $company) {
            $company = Company::whereKey($company->id)->lockForUpdate()->firstOrFail();

            $company->update(['name' => $request->validated('name')]);

            return $company;
        });

        return to_route('companies.edit', ['company' => $company->slug]);
    }

    /**
     * Switch the user's current company.
     */
    public function switch(Request $request, Company $company): RedirectResponse
    {
        abort_unless($request->user()->belongsToCompany($company), 403);

        $request->user()->switchCompany($company);

        return back();
    }

    /**
     * Delete the specified company.
     */
    public function destroy(DeleteCompanyRequest $request, Company $company): RedirectResponse
    {
        $user = $request->user();
        $fallbackCompany = $user->isCurrentCompany($company)
            ? $user->fallbackCompany($company)
            : null;

        DB::transaction(function () use ($user, $company) {
            User::where('current_company_id', $company->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchCompany($affectedUser->personalCompany()));

            $company->invitations()->delete();
            $company->memberships()->delete();
            $company->delete();
        });

        if ($fallbackCompany) {
            $user->switchCompany($fallbackCompany);
        }

        return to_route('companies.index');
    }
}
