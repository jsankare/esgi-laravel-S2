<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\JoinRoomRequest;
use Illuminate\Http\Request;
use App\Models\Message;

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

        $messages = Message::where('room_id', $room->id)->get();

        $room->load('users');
        return view('rooms.show', compact('room', 'messages'));
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

    public function edit(Room $room)
    {
        if ($room->creator_id !== auth()->id()) {
            return redirect()->route('rooms.index')
                ->with('error', 'Only the creator can edit the room.');
        }

        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        // Vérification que seul le créateur peut modifier la room
        if ($room->creator_id !== auth()->id()) {
            return redirect()->route('rooms.index')
                ->with('error', 'Only the creator can update the room.');
        }
    
        // Validation des autres champs du formulaire
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        // Si un mot de passe actuel est spécifié et que la room a déjà un mot de passe, vérifier sa validité
        if ($request->filled('current_password') && $room->password && $request->current_password !== $room->password) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
    
        // Si un nouveau mot de passe est spécifié, le mettre à jour sans le hacher
        if ($request->filled('new_password')) {
            $room->password = $request->new_password;  // Pas de hachage ici
        }
    
        // Si l'option "remove password" est cochée, retirer le mot de passe
        if ($request->has('remove_password') && $request->remove_password) {
            $room->password = null;  // Supprimer le mot de passe de la room
        }
    
        // Mise à jour des autres informations de la room
        $room->update([
            'name' => $request->name,
        ]);
    
        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully.');
    }

}
