<?php

namespace App\Http\Controllers;

use App\Models\HourlyRate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class HourlyRateController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, string $current_team)
    {
        $this->authorize('create', HourlyRate::class);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'rate' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        $request->user()->currentTeam->hourlyRates()->create($validated);

        return back()->with('flash', ['message' => 'Hourly rate added.']);
    }

    public function update(Request $request, string $current_team, HourlyRate $hourlyRate)
    {
        $this->authorize('update', $hourlyRate);

        $validated = $request->validate([
            'rate' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        $hourlyRate->update($validated);

        return back()->with('flash', ['message' => 'Hourly rate updated.']);
    }

    public function destroy(Request $request, string $current_team, HourlyRate $hourlyRate)
    {
        $this->authorize('delete', $hourlyRate);

        $hourlyRate->delete();

        return back()->with('flash', ['message' => 'Hourly rate deleted.']);
    }
}
