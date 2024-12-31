<?php

use App\Http\Controllers\MovieController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Route::get('/Movies', [MovieController::class, 'index'])->name('Movies.index');
    // Route::post('/Movies', [MovieController::class, 'store'])->name('Movies.store');
    // Route::get('/Movies/{Movie}', [MovieController::class, 'edit'])->name('Movies.edit');
    // Route::match(['put', 'patch'], '/Movies/{Movie}', [MovieController::class, 'update'])->name('Movies.update');
    // Route::delete('/Movies/{Movie}', [MovieController::class, 'destroy'])->name('Movies.destroy');
    Route::resource('Movies', MovieController::class)->except(['create', 'show']);
    // ->only(['index', 'store', 'edit', 'update', 'destroy']);
});
