<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\TagsController;
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

    Route::prefix('management')->name('management.')->group(function () {
        Route::resource('users', UsersController::class)->except(['create', 'edit']);
        Route::resource('roles', RolesController::class)->except(['create', 'edit', 'show']);
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::post('attach', [RolesController::class, 'attach'])->name('attach');
            Route::post('detach', [RolesController::class, 'detach'])->name('detach');
        });
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::get('activity-log/export/{format}', [ActivityLogController::class, 'export'])->name('activity-log.export');
    });

    Route::resource('tags', TagsController::class)->except(['create', 'edit']);

    Route::prefix('api')->group(function() {
        Route::get('/tags', [TagsController::class, 'all'])->name('tags.all');
        Route::get('/roles', [RolesController::class, 'all'])->name('roles.all');
    });

});

require __DIR__.'/settings.php';
