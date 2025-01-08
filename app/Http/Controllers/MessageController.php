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

        // Retourner une réponse JSON avec les données nécessaires pour mettre à jour la page
        return response()->json([
            'success' => true,
            'user_name' => $message->user->name,
            'message' => $message->message
        ]);
    } catch (\Exception $e) {
        // Si une erreur se produit, retourner une réponse JSON avec un message d'erreur
        return response()->json([
            'success' => false,
            'error' => 'Message could not be sent'
        ], 500);
    }
}

}
