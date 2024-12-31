<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     */
    public function index()
    {
        $rooms = Room::all();
        return response()->json($rooms);
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        $room = Room::create($validated);

        return response()->json($room, 201);
    }

    /**
     * Display the specified room.
     */
    public function show(Room $room)
    {
        return response()->json($room);
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'password' => 'nullable|string|max:255',
        ]);

        $room->update($validated);

        return response()->json($room);
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json(['message' => 'Room deleted successfully.']);
    }
}
