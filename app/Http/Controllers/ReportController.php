<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request, string $current_team): Response
    {
        $team = $request->user()->currentTeam;

        $projects = Project::whereIn('client_id', $team->clients()->pluck('id'))
            ->with(['client'])
            ->get();

        // Basic aggregation for demonstration
        $timeByProject = TimeEntry::whereIn('task_id', function ($query) use ($projects) {
            $query->select('id')->from('tasks')->whereIn('project_id', $projects->pluck('id'));
        })
            ->selectRaw('sum(duration_seconds) as total_seconds, tasks.project_id')
            ->join('tasks', 'time_entries.task_id', '=', 'tasks.id')
            ->groupBy('tasks.project_id')
            ->get()
            ->pluck('total_seconds', 'project_id');

        $reports = $projects->map(function ($project) use ($timeByProject) {
            $seconds = $timeByProject[$project->id] ?? 0;

            return [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'client_name' => $project->client->name,
                'total_hours' => round($seconds / 3600, 2),
                'total_budget' => $project->total_budget,
                'status' => $project->status,
            ];
        });

        return Inertia::render('Reports/Index', [
            'projectReports' => $reports,
        ]);
    }
}
