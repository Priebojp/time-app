<?php

namespace App\Policies;

use App\Enums\TeamPermission;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
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
    public function view(User $user, Task $task): bool
    {
        return $user->belongsToTeam($task->project->client->team);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentTeam && $user->hasTeamPermission($user->currentTeam, TeamPermission::CreateTasks);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasTeamPermission($task->project->client->team, TeamPermission::UpdateTasks);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasTeamPermission($task->project->client->team, TeamPermission::DeleteTasks);
    }
}
