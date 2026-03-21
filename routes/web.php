<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CutoffController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::resource('expenses', ExpensesController::class);
    Route::resource('cutoff', CutoffController::class);
    Route::resource('accounts', AccountsController::class);
    Route::resource('roles', RolesController::class)
        ->only(['index', 'store', 'update', 'destroy']);

});

require __DIR__.'/settings.php';
