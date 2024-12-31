<?php

use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Route::get('/Rooms', [RoomController::class, 'index'])->name('Rooms.index');
    // Route::post('/Rooms', [RoomController::class, 'store'])->name('Rooms.store');
    // Route::get('/Rooms/{Room}', [RoomController::class, 'edit'])->name('Rooms.edit');
    // Route::match(['put', 'patch'], '/Rooms/{Room}', [RoomController::class, 'update'])->name('Rooms.update');
    // Route::delete('/Rooms/{Room}', [RoomController::class, 'destroy'])->name('Rooms.destroy');
    Route::resource('Rooms', RoomController::class)->except(['create', 'show']);
    // ->only(['index', 'store', 'edit', 'update', 'destroy']);
});
