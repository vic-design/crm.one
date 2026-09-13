<?php

use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Route::get('/', function () {
//     return redirect(route('dashboard'));
// })->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('users', UsersController::class)->except(['create', 'edit', 'show']);

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::resource('', RolesController::class)->except(['create', 'edit', 'show']);

        Route::post('attach', [RolesController::class, 'attach'])->name('attach');
        Route::post('detach', [RolesController::class, 'detach'])->name('detach');
    });
});

require __DIR__.'/settings.php';
