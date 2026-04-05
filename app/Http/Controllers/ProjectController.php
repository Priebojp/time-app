<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, string $current_company): Response
    {
        $this->authorize('viewAny', Project::class);

        return Inertia::render('Projects/Index', [
            'projects' => Project::whereIn('client_id', $request->user()->currentCompany->clients()->pluck('id'))
                ->with('client')
                ->get(),
            'clients' => $request->user()->currentCompany->clients()->get(),
        ]);
    }

    public function store(Request $request, string $current_company)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_budget' => ['nullable', 'numeric', 'min:0'],
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        Project::create($validated);

        return back()->with('flash', ['message' => 'Project created successfully.']);
    }

    public function show(string $current_company, Project $project): Response
    {
        $this->authorize('view', $project);

        return Inertia::render('Projects/Show', [
            'project' => $project->load(['client', 'tasks.users', 'users']),
        ]);
    }

    public function update(Request $request, string $current_company, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'total_budget' => ['nullable', 'numeric', 'min:0'],
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'in:active,archived,completed'],
        ]);

        $project->update($validated);

        return back()->with('flash', ['message' => 'Project updated successfully.']);
    }

    public function destroy(string $current_company, Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return back()->with('flash', ['message' => 'Project deleted successfully.']);
    }
}
