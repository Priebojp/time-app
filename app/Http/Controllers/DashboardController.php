<?php

namespace App\Http\Controllers;

use App\Enums\CompanyRole;
use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function __invoke(Request $request, string $current_company): Response
    {
        $company = Company::where('slug', $current_company)->firstOrFail();
        $user = $request->user();

        // Let's assume 'admin' if they have 'admin' or 'owner' role.
        $role = $user->companyRole($company);
        if ($role === CompanyRole::Admin || $role === CompanyRole::Owner) {
            return $this->adminDashboard($company);
        }

        return $this->memberDashboard($company, $user);
    }

    /**
     * Show the admin dashboard.
     */
    protected function adminDashboard(Company $company): Response
    {
        return Inertia::render('Dashboard', [
            'role' => 'admin',
            'stats' => [
                'total_projects' => $company->projects()->count(),
                'total_clients' => $company->clients()->count(),
                'active_tasks' => Task::whereIn('project_id', $company->projects()->pluck('projects.id'))
                    ->where('status', '!=', 'completed')
                    ->count(),
                'company_members' => $company->members()->count(),
            ],
        ]);
    }

    /**
     * Show the member dashboard.
     */
    protected function memberDashboard(Company $company, $user): Response
    {
        $lastTimeEntry = $user->timeEntries()
            ->whereHas('task.project.client', function ($query) use ($company) {
                $query->where('company_id', $company->id);
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

        $availableProjects = Project::whereHas('client', function ($query) use ($company) {
            $query->where('company_id', $company->id);
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
