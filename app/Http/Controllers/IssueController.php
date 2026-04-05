<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IssueController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, string $current_team): Response
    {
        $this->authorize('viewAny', Issue::class);

        $team = $request->user()->currentTeam;

        return Inertia::render('Kanban/Index', [
            'issues' => $team->issues()
                ->with(['reporter', 'assignee', 'project'])
                ->orderBy('order_index')
                ->get(),
            'projects' => Project::whereIn('client_id', $team->clients()->pluck('id'))->get(),
            'members' => $team->members()->get(),
        ]);
    }

    public function store(Request $request, string $current_team)
    {
        $this->authorize('create', Issue::class);

        $validated = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'status' => ['required', 'string', 'in:todo,in_progress,review,done'],
        ]);

        $request->user()->currentTeam->issues()->create([
            ...$validated,
            'reporter_id' => $request->user()->id,
            'order_index' => $request->user()->currentTeam->issues()->count(),
        ]);

        return back()->with('flash', ['message' => 'Issue created.']);
    }

    public function update(Request $request, string $current_team, Issue $issue)
    {
        $this->authorize('update', $issue);

        $validated = $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'status' => ['required', 'string', 'in:todo,in_progress,review,done'],
            'order_index' => ['nullable', 'integer'],
        ]);

        $issue->update($validated);

        return back()->with('flash', ['message' => 'Issue updated.']);
    }

    public function destroy(string $current_team, Issue $issue)
    {
        $this->authorize('delete', $issue);

        $issue->delete();

        return back()->with('flash', ['message' => 'Issue deleted.']);
    }
}
