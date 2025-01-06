<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Movie;
use App\Events\EliminationStarted;
use App\Events\MovieEliminated;
use App\Events\EliminationCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomEliminationController extends Controller
{
    public function start(Request $request, Room $room)
    {
        if ($room->creator_id !== Auth::id()) {
            return response()->json(['error' => 'Only the room creator can start elimination'], 403);
        }

        if (!$room->canStartElimination()) {
            return response()->json(['error' => 'Cannot start elimination'], 422);
        }

        $room->update([
            'elimination_started' => true,
            'elimination_in_progress' => true,
            'last_elimination_at' => now(),
        ]);

        broadcast(new EliminationStarted($room))->toOthers();

        return response()->json(['message' => 'Elimination started']);
    }

    public function eliminate(Request $request, Room $room)
    {
        if (!$room->elimination_in_progress) {
            return response()->json(['error' => 'No elimination in progress'], 422);
        }

        $remainingMovies = $room->movies()
            ->wherePivot('eliminated_at', null)
            ->count();

        if ($remainingMovies <= 1) {
            $room->update(['elimination_in_progress' => false]);
            $winner = $room->movies()
                ->wherePivot('eliminated_at', null)
                ->first();

            broadcast(new EliminationCompleted($room, $winner))->toOthers();

            return response()->json([
                'status' => 'completed',
                'winner' => $winner
            ]);
        }

        $movieToEliminate = $room->movies()
            ->wherePivot('eliminated_at', null)
            ->inRandomOrder()
            ->first();

        if ($movieToEliminate) {
            $room->movies()->updateExistingPivot($movieToEliminate->id, [
                'eliminated_by' => Auth::id(),
                'eliminated_at' => now(),
            ]);

            $room->update(['last_elimination_at' => now()]);

            $data = [
                'status' => 'eliminated',
                'eliminated_movie' => $movieToEliminate,
                'remaining_count' => $remainingMovies - 1
            ];

            broadcast(new MovieEliminated($room, $data))->toOthers();

            return response()->json($data);
        }

        return response()->json(['error' => 'No movies to eliminate'], 422);
    }

    public function status(Room $room)
    {
        return response()->json([
            'elimination_started' => $room->elimination_started,
            'elimination_in_progress' => $room->elimination_in_progress,
            'remaining_movies' => $room->movies()
                ->wherePivot('eliminated_at', null)
                ->get(),
            'eliminated_movies' => $room->movies()
                ->wherePivot('eliminated_at', '!=', null)
                ->orderByPivot('eliminated_at', 'desc')
                ->get()
        ]);
    }

    public function reset(Request $request, Room $room)
    {
        if ($room->creator_id !== Auth::id()) {
            return response()->json(['error' => 'Only the room creator can reset elimination'], 403);
        }

        DB::transaction(function () use ($room) {
            // Reset elimination data in pivot table
            $room->movies()->update([
                'room_movie.eliminated_by' => null,
                'room_movie.eliminated_at' => null
            ]);

            $room->update([
                'elimination_started' => false,
                'elimination_in_progress' => false,
                'last_elimination_at' => null
            ]);
        });

        return response()->json(['message' => 'Elimination reset successfully']);
    }
}
