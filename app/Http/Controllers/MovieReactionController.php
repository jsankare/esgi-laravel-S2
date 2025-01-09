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
            ->groupBy(['movie_id', 'emoji'])
            ->map(function ($movieReactions) use ($room) {
                return $movieReactions->map(function ($emojiReactions) {
                    return [
                        'movie_id' => $emojiReactions->first()->movie_id,
                        'emoji' => $emojiReactions->first()->emoji,
                        'count' => $emojiReactions->count(),
                        'user_reacted' => $emojiReactions->contains('user_id', Auth::id())
                    ];
                })->values();
            })
            ->flatten(1);

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

        // Remove user reaction
        MovieReaction::where([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'movie_id' => $movie->id,
        ])->delete();

        // Create new reaction
        MovieReaction::create([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'movie_id' => $movie->id,
            'emoji' => $validated['emoji']
        ]);

        $count = MovieReaction::where('room_id', $room->id)
            ->where('movie_id', $movie->id)
            ->where('emoji', $validated['emoji'])
            ->count();

        return response()->json([
            'success' => true,
            'reaction' => [
                'count' => $count,
                'user_reacted' => true
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
