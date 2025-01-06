<?php

namespace App\Events;

use App\Models\Room;
use App\Models\Movie;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EliminationCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $room;
    public $winner;

    public function __construct(Room $room, Movie $winner)
    {
        $this->room = $room;
        $this->winner = $winner;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.' . $this->room->id)
        ];
    }
}
