<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\HourlyRate;
use App\Models\User;

class HourlyRatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentTeam !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, HourlyRate $hourlyRate): bool
    {
        return $user->id === $hourlyRate->user_id || $user->hasTeamPermission($hourlyRate->team, TeamPermission::UpdateHourlyRates);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentTeam && $user->hasTeamPermission($user->currentTeam, TeamPermission::CreateHourlyRates);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HourlyRate $hourlyRate): bool
    {
        return $user->hasTeamPermission($hourlyRate->team, TeamPermission::UpdateHourlyRates);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, HourlyRate $hourlyRate): bool
    {
        return $user->hasTeamPermission($hourlyRate->team, TeamPermission::DeleteHourlyRates);
    }
}
