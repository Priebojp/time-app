<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, string $current_team): Response
    {
        $this->authorize('viewAny', Position::class);

        return Inertia::render('Positions/Index', [
            'positions' => $request->user()->currentTeam->positions()->get(),
        ]);
    }

    public function store(Request $request, string $current_team)
    {
        $this->authorize('create', Position::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $request->user()->currentTeam->positions()->create($validated);

        return back()->with('flash', ['message' => 'Position created successfully.']);
    }

    public function update(Request $request, string $current_team, Position $position)
    {
        $this->authorize('update', $position);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $position->update($validated);

        return back()->with('flash', ['message' => 'Position updated successfully.']);
    }

    public function destroy(string $current_team, Position $position)
    {
        $this->authorize('delete', $position);

        $position->delete();

        return back()->with('flash', ['message' => 'Position deleted successfully.']);
    }
}
