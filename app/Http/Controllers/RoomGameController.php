<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomGameController extends Controller
{
    public function startGame(Room $room)
    {
        if ($room->creator_id !== auth()->id()) {
            return response()->json(['error' => 'Only the room creator can start the game.'], 403);
        }

        if ($room->game_started) {
            return response()->json(['error' => 'Game has already started.'], 422);
        }

        $movieCount = $room->movies()->count();
        if ($movieCount < 2) {
            return response()->json(['error' => 'Need at least 2 movies to start the game.'], 422);
        }

        DB::transaction(function () use ($room) {
            $room->update([
                'game_started' => true,
                'current_player_id' => $room->users->random()->id
            ]);
        });

        return response()->json(['message' => 'Game started successfully.']);
    }

    public function eliminateMovie(Request $request, Room $room, Movie $movie)
    {
        if (!$room->game_started) {
            return response()->json(['error' => 'Game has not started yet.'], 422);
        }

        if ($room->current_player_id !== auth()->id()) {
            return response()->json(['error' => 'It\'s not your turn.'], 403);
        }

        if ($movie->eliminated_at) {
            return response()->json(['error' => 'This movie has already been eliminated.'], 422);
        }

        DB::transaction(function () use ($room, $movie) {
            // Eliminate the movie
            $movie->update([
                'eliminated_by' => auth()->id(),
                'eliminated_at' => now()
            ]);

            // Get next player
            $nextPlayer = $room->users()
                ->where('id', '>', $room->current_player_id)
                ->orderBy('id')
                ->first();

            if (!$nextPlayer) {
                $nextPlayer = $room->users()->orderBy('id')->first();
            }

            $room->update(['current_player_id' => $nextPlayer->id]);
        });

        return response()->json(['message' => 'Movie eliminated successfully.']);
    }
}
