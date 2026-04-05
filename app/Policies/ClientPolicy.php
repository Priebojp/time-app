<?php

namespace App\Policies;

use App\Enums\CompanyPermission;
use App\Models\Client;
use App\Models\User;

class ClientPolicy
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
    public function view(User $user, Client $client): bool
    {
        return $user->belongsToCompany($client->company);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentCompany && $user->hasCompanyPermission($user->currentCompany, CompanyPermission::CreateClients);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->hasCompanyPermission($client->company, CompanyPermission::UpdateClients);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->hasCompanyPermission($client->company, CompanyPermission::DeleteClients);
    }
}
