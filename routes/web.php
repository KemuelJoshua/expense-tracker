<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CutoffController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserApprovalController;
use App\Http\Middleware\EnsureUserIsApproved;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('pending-approval', function () {
        $user = request()->user();

        if ($user !== null && (! $user->hasRole('EndUser') || $user->is_approved)) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/PendingApproval');
    })->name('approval.pending');
});

Route::middleware(['auth', 'verified', EnsureUserIsApproved::class])->group(function () {
    Route::inertia('dashboard', 'Dashboard')
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::resource('expenses', ExpensesController::class);
    Route::resource('cutoff', CutoffController::class);
    Route::resource('accounts', AccountsController::class);
    Route::resource('approvals', UserApprovalController::class)
        ->only(['index', 'update']);
    Route::resource('roles', RolesController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
