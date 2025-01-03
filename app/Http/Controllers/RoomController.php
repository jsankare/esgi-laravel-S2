<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\JoinRoomRequest;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('users')->get();
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(StoreRoomRequest $request)
    {
        $validated = $request->validated();
        $validated['creator_id'] = auth()->id();

        $room = Room::create($validated);
        $room->users()->attach(auth()->id());

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        // Check if user is a member
        if (!$room->users->contains(auth()->id())) {
            return redirect()->route('rooms.index')
                ->with('error', 'You must be a member to view room details.');
        }

        $room->load('users');
        return view('rooms.show', compact('room'));
    }

    public function join(JoinRoomRequest $request, Room $room)
    {
        // Check if user is already in the room
        if ($room->users->contains(auth()->id())) {
            return redirect()->route('rooms.show', $room);
        }

        // Check password if room has one
        if ($room->password && $request->password !== $room->password) {
            return back()->withErrors([
                'password' => 'Incorrect password.',
            ]);
        }

        $room->users()->attach(auth()->id());
        return redirect()->route('rooms.show', $room)
            ->with('success', 'Joined room successfully.');
    }

    public function destroy(Room $room)
    {
        if ($room->creator_id !== auth()->id()) {
            return redirect()->route('rooms.index')
                ->with('error', 'Only the creator can delete the room.');
        }

        $room->delete();
        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully.');
    }
}
