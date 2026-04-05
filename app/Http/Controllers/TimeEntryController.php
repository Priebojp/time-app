<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimeEntryController extends Controller
{
    use AuthorizesRequests;

    public function start(Request $request, string $current_company)
    {
        $this->authorize('create', TimeEntry::class);

        $validated = $request->validate([
            'task_id' => ['required', 'exists:tasks,id'],
            'note' => ['nullable', 'string'],
        ]);

        // Stop any currently running timer
        $request->user()->activeTimeEntry?->stop();

        $request->user()->timeEntries()->create([
            'task_id' => $validated['task_id'],
            'started_at' => Carbon::now(),
            'note' => $validated['note'] ?? null,
            'is_running' => true,
        ]);

        return back()->with('flash', ['message' => 'Timer started.']);
    }

    public function stop(Request $request, string $current_company, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        $timeEntry->stop();

        return back()->with('flash', ['message' => 'Timer stopped.']);
    }

    public function update(Request $request, string $current_company, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        $validated = $request->validate([
            'note' => ['nullable', 'string'],
            'started_at' => ['required', 'date'],
            'stopped_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);

        $timeEntry->update($validated);

        if ($timeEntry->stopped_at) {
            $timeEntry->update([
                'duration_seconds' => $timeEntry->started_at->diffInSeconds($timeEntry->stopped_at),
                'is_running' => false,
            ]);
        }

        return back()->with('flash', ['message' => 'Time entry updated.']);
    }

    public function destroy(Request $request, string $current_company, TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);

        $timeEntry->delete();

        return back()->with('flash', ['message' => 'Time entry deleted.']);
    }
}
