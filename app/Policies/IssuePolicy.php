<?php

namespace App\Policies;

use App\Enums\CompanyPermission;
use App\Models\Issue;
use App\Models\User;

class IssuePolicy
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
    public function view(User $user, Issue $issue): bool
    {
        return $user->belongsToCompany($issue->company);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Issue $issue): bool
    {
        return $user->id === $issue->reporter_id
            || $user->id === $issue->assignee_id
            || $user->hasCompanyPermission($issue->company, CompanyPermission::UpdateIssues);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Issue $issue): bool
    {
        return $user->id === $issue->reporter_id
            || $user->hasCompanyPermission($issue->company, CompanyPermission::DeleteIssues);
    }
}
