<?php

namespace App\Http\Controllers;

use App\Enums\TeamRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function __invoke(Request $request, string $current_team): Response
    {
        $team = Team::where('slug', $current_team)->firstOrFail();
        $user = $request->user();

        // Let's assume 'admin' if they have 'admin' or 'owner' role.
        $role = $user->teamRole($team);
        if ($role === TeamRole::Admin || $role === TeamRole::Owner) {
            return $this->adminDashboard($team);
        }

        return $this->memberDashboard($team, $user);
    }

    /**
     * Show the admin dashboard.
     */
    protected function adminDashboard(Team $team): Response
    {
        return Inertia::render('Dashboard', [
            'role' => 'admin',
            'stats' => [
                'total_projects' => $team->projects()->count(),
                'total_clients' => $team->clients()->count(),
                'active_tasks' => Task::whereIn('project_id', $team->projects()->pluck('projects.id'))
                    ->where('status', '!=', 'completed')
                    ->count(),
                'team_members' => $team->members()->count(),
            ],
        ]);
    }

    /**
     * Show the member dashboard.
     */
    protected function memberDashboard(Team $team, $user): Response
    {
        $lastTimeEntry = $user->timeEntries()
            ->whereHas('task.project.client', function ($query) use ($team) {
                $query->where('team_id', $team->id);
            })
            ->with(['task.project'])
            ->latest('started_at')
            ->first();

        $weekHours = $user->timeEntries()
            ->whereBetween('started_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('duration_seconds') / 3600;

        $monthHours = $user->timeEntries()
            ->whereBetween('started_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('duration_seconds') / 3600;

        $availableProjects = Project::whereHas('client', function ($query) use ($team) {
            $query->where('team_id', $team->id);
        })
            ->where('status', 'active')
            ->with(['tasks' => function ($query) {
                $query->where('status', '!=', 'completed');
            }])
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'tasks' => $project->tasks->map(fn ($task) => [
                        'id' => $task->id,
                        'name' => $task->name,
                    ]),
                ];
            });

        return Inertia::render('Dashboard', [
            'role' => 'member',
            'stats' => [
                'last_project' => $lastTimeEntry?->task?->project,
                'week_hours' => round($weekHours, 2),
                'month_hours' => round($monthHours, 2),
                'current_status' => $user->activeTimeEntry ? 'working' : 'idle',
            ],
            'available_projects' => $availableProjects,
        ]);
    }
}
