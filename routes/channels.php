<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    $room = \App\Models\Room::find($roomId);
    return $room && $room->users->contains($user->id);
});
