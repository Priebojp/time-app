<?php

namespace App\Policies;

use App\Enums\CompanyPermission;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
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
    public function view(User $user, Task $task): bool
    {
        return $user->belongsToCompany($task->project->client->company);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentCompany && $user->hasCompanyPermission($user->currentCompany, CompanyPermission::CreateTasks);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasCompanyPermission($task->project->client->company, CompanyPermission::UpdateTasks);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasCompanyPermission($task->project->client->company, CompanyPermission::DeleteTasks);
    }
}
