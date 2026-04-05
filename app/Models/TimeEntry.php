<?php

namespace App\Models;

use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'started_at',
        'stopped_at',
        'duration_seconds',
        'note',
        'is_running',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
            'is_running' => 'boolean',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * Get the user that owns the time entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the task that the time entry is for.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Stop the time entry.
     */
    public function stop(): void
    {
        if (! $this->is_running) {
            return;
        }

        $stoppedAt = Carbon::now();
        $duration = $this->started_at->diffInSeconds($stoppedAt);

        $this->update([
            'stopped_at' => $stoppedAt,
            'duration_seconds' => $duration,
            'is_running' => false,
        ]);
    }
}
