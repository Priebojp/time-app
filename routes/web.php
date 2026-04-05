<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\Companies\CompanyInvitationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HourlyRateController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Middleware\EnsureCompanyMembership;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::prefix('{current_company}')
    ->middleware(['auth', 'verified', EnsureCompanyMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::resource('positions', PositionController::class)
            ->except(['create', 'edit', 'show']);

        Route::resource('clients', ClientController::class)
            ->except(['create', 'edit', 'show']);

        Route::resource('projects', ProjectController::class)
            ->except(['create', 'edit']);

        Route::resource('tasks', TaskController::class)
            ->only(['store', 'update', 'destroy']);

        Route::resource('hourly-rates', HourlyRateController::class)
            ->only(['store', 'update', 'destroy']);

        Route::resource('issues', IssueController::class)
            ->except(['create', 'edit', 'show']);

        Route::get('reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::post('time-entries/start', [TimeEntryController::class, 'start'])->name('time-entries.start');
        Route::post('time-entries/{time_entry}/stop', [TimeEntryController::class, 'stop'])->name('time-entries.stop');
        Route::patch('time-entries/{time_entry}', [TimeEntryController::class, 'update'])->name('time-entries.update');
        Route::delete('time-entries/{time_entry}', [TimeEntryController::class, 'destroy'])->name(
            'time-entries.destroy'
        );
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [CompanyInvitationController::class, 'accept'])->name(
        'invitations.accept'
    );
});

require __DIR__.'/settings.php';
