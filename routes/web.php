<?php

use App\Http\Controllers\CutoffController;
use App\Http\Controllers\ExpensesController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('expenses', ExpensesController::class);
    Route::resource('cutoff', CutoffController::class);

});

require __DIR__.'/settings.php';
