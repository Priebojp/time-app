<?php

namespace App\Policies;

use App\Enums\CompanyPermission;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->belongsToCompany($project->client->company);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentCompany && $user->hasCompanyPermission($user->currentCompany, CompanyPermission::CreateProjects);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->hasCompanyPermission($project->client->company, CompanyPermission::UpdateProjects);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasCompanyPermission($project->client->company, CompanyPermission::DeleteProjects);
    }
}
