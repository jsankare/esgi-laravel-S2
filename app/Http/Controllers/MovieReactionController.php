<?php

namespace App\Http\Controllers;

use App\Models\MovieReaction;
use App\Models\Room;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieReactionController extends Controller
{
    public function index(Room $room)
    {
        $reactions = MovieReaction::where('room_id', $room->id)
            ->with('user')
            ->get()
            ->map(function ($reaction) {
                return [
                    'movie_id' => $reaction->movie_id,
                    'emoji' => $reaction->emoji,
                    'count' => MovieReaction::where('room_id', $reaction->room_id)
                        ->where('movie_id', $reaction->movie_id)
                        ->where('emoji', $reaction->emoji)
                        ->count(),
                    'user_reacted' => $reaction->user_id === Auth::id()
                ];
            })
            ->unique(function ($reaction) {
                return $reaction['movie_id'] . $reaction['emoji'];
            })
            ->values();

        return response()->json($reactions);
    }

    public function store(Request $request, Room $room, Movie $movie)
    {
        $validated = $request->validate([
            'emoji' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!in_array($value, MovieReaction::$supportedEmojis)) {
                    $fail('The selected emoji is not supported.');
                }
            }],
        ]);

        // Check if user already has this reaction
        $existingReaction = MovieReaction::where([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'emoji' => $validated['emoji']
        ])->first();

        if ($existingReaction) {
            // If reaction exists, remove it (toggle behavior)
            $existingReaction->delete();
            $userReacted = false;
        } else {
            // Create new reaction
            MovieReaction::create([
                'user_id' => Auth::id(),
                'room_id' => $room->id,
                'movie_id' => $movie->id,
                'emoji' => $validated['emoji']
            ]);
            $userReacted = true;
        }

        $count = MovieReaction::where('room_id', $room->id)
            ->where('movie_id', $movie->id)
            ->where('emoji', $validated['emoji'])
            ->count();

        return response()->json([
            'success' => true,
            'reaction' => [
                'count' => $count,
                'user_reacted' => $userReacted
            ]
        ]);
    }

    public function destroy(Room $room, Movie $movie, Request $request)
    {
        $validated = $request->validate([
            'emoji' => ['required', 'string']
        ]);

        $deleted = MovieReaction::where([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'emoji' => $validated['emoji']
        ])->delete();

        $count = MovieReaction::where('room_id', $room->id)
            ->where('movie_id', $movie->id)
            ->where('emoji', $validated['emoji'])
            ->count();

        return response()->json([
            'success' => true,
            'reaction' => [
                'count' => $count,
                'user_reacted' => false
            ]
        ]);
    }
}
