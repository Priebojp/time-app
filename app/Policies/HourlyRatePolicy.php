<?php

namespace App\Policies;

use App\Enums\CompanyPermission;
use App\Models\HourlyRate;
use App\Models\User;

class HourlyRatePolicy
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
    public function view(User $user, HourlyRate $hourlyRate): bool
    {
        return $user->id === $hourlyRate->user_id || $user->hasCompanyPermission($hourlyRate->company, CompanyPermission::UpdateHourlyRates);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentCompany && $user->hasCompanyPermission($user->currentCompany, CompanyPermission::CreateHourlyRates);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HourlyRate $hourlyRate): bool
    {
        return $user->hasCompanyPermission($hourlyRate->company, CompanyPermission::UpdateHourlyRates);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, HourlyRate $hourlyRate): bool
    {
        return $user->hasCompanyPermission($hourlyRate->company, CompanyPermission::DeleteHourlyRates);
    }
}
