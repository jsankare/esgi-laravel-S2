<?php

use App\Http\Controllers\MovieReactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\RoomMovieController;
use App\Http\Controllers\RoomEliminationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ActorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rooms routes
    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms/{room}/join', [RoomController::class, 'join'])->name('rooms.join');
    Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
    Route::match(['put', 'patch'], '/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
    Route::post('/rooms/{room}/sendMessage', [MessageController::class, 'storeMessage'])->name('messages.store');

    // Room Movies routes
    Route::get('/rooms/{room}/movies/search', [RoomMovieController::class, 'search']);
    Route::get('/rooms/{room}/movies', [RoomMovieController::class, 'index']);
    Route::post('/rooms/{room}/movies', [RoomMovieController::class, 'store']);
    Route::delete('/rooms/{room}/movies/{movie}', [RoomMovieController::class, 'destroy']);

    // Movies routes
    Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
    Route::get('/movies/{imdbId}', [MovieController::class, 'show'])->name('movies.show');

    // Movie Reactions routes
    Route::post('/rooms/{room}/movies/{movie}/reactions', [MovieReactionController::class, 'store'])->name('movie.reactions.store');
    Route::delete('/rooms/{room}/movies/{movie}/reactions', [MovieReactionController::class, 'destroy'])->name('movie.reactions.destroy');


    // Actor routes
    Route::get('/actor/{actor}', [ActorController::class, 'show'])->name('actor.show');

    // Room Elimination routes
    Route::post('/rooms/{room}/elimination/start', [RoomEliminationController::class, 'start']);
    Route::post('/rooms/{room}/elimination/eliminate', [RoomEliminationController::class, 'eliminate']);
    Route::get('/rooms/{room}/elimination/status', [RoomEliminationController::class, 'status']);
    Route::post('/rooms/{room}/elimination/reset', [RoomEliminationController::class, 'reset']);

});

require __DIR__.'/auth.php';
