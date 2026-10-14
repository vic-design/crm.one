<?php

use App\Http\Controllers\RolesController;
use App\Http\Controllers\TagsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/tags', [TagsController::class, 'all'])->name('tags.all');
    Route::get('/roles', [RolesController::class, 'all'])->name('roles.all');
});
