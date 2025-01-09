<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    // Méthode pour afficher les messages du chat d'une room
    public function show(Room $room)
    {
        if (!auth()->user()->rooms->contains($room)) {
            return redirect()->route('rooms.index')->with('error', 'You do not have access to this room.');
        }
        $messages = $room->messages()->latest()->get();
        return view('message.show', compact('room', 'messages'));
    }

    // Méthode pour envoyer un message
    public function storeMessage(Request $request, $roomId)
    {
    $validated = $request->validate([
        'message' => 'required|string',
        'parent_message_id' => 'nullable|exists:messages,id'
    ]);

    try {
        // Création du message
        $message = Message::create([
            'room_id' => $roomId,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'parent_message_id' => $validated['parent_message_id']
        ]);
        return redirect()->route('rooms.show', $room)->with('success', 'Message sent successfully.');

    } catch (\Exception $e) {
        return back()->with('error', 'An error occurred while sending the message.');
    }
}

}
