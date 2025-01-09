<?php

namespace App\Http\Controllers;

use App\Models\MovieReaction;
use App\Models\Room;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieReactionController extends Controller
{
    public function store(Request $request, Room $room, Movie $movie)
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:8',
        ]);

        MovieReaction::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'room_id' => $room->id,
                'movie_id' => $movie->id,
            ],
            ['emoji' => $validated['emoji']]
        );

        return response()->json([
            'success' => true,
            'message' => 'Reaction added successfully'
        ]);
    }

    public function destroy(Room $room, Movie $movie)
    {
        MovieReaction::where([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'movie_id' => $movie->id,
        ])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reaction removed successfully'
        ]);
    }
}
