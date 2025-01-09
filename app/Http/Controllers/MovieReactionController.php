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
            'emoji' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!in_array($value, MovieReaction::$supportedEmojis)) {
                    $fail('The selected emoji is not supported.');
                }
            }],
        ]);

        $reaction = MovieReaction::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'room_id' => $room->id,
                'movie_id' => $movie->id,
            ],
            ['emoji' => $validated['emoji']]
        );

        return response()->json([
            'success' => true,
            'reaction' => $reaction,
            'user_name' => Auth::user()->name
        ]);
    }

    public function destroy(Room $room, Movie $movie)
    {
        MovieReaction::where([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'movie_id' => $movie->id,
        ])->delete();

        return response()->json(['success' => true]);
    }
}
