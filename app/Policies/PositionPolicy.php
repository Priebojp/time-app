<?php

namespace App\Policies;

use App\Enums\CompanyPermission;
use App\Models\Position;
use App\Models\User;

class PositionPolicy
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
    public function view(User $user, Position $position): bool
    {
        return $user->belongsToCompany($position->company);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentCompany && $user->hasCompanyPermission($user->currentCompany, CompanyPermission::CreatePositions);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Position $position): bool
    {
        return $user->hasCompanyPermission($position->company, CompanyPermission::UpdatePositions);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Position $position): bool
    {
        return $user->hasCompanyPermission($position->company, CompanyPermission::DeletePositions);
    }
}
