<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::all();
        $users = User::all();

        foreach ($rooms as $room) {
            // Get users in this room
            $roomUsers = $room->users;

            if ($roomUsers->isEmpty()) {
                continue;
            }

            // Create 5-10 parent messages for each room
            for ($i = 0; $i < rand(5, 10); $i++) {
                $user = $roomUsers->random();
                $message = Message::create([
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'message' => fake()->realText(rand(50, 200)),
                    'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
                ]);

                // 50% chance to add 1-3 replies to this message
                if (rand(0, 1)) {
                    for ($j = 0; $j < rand(1, 3); $j++) {
                        $replyUser = $roomUsers->random();
                        Message::create([
                            'room_id' => $room->id,
                            'user_id' => $replyUser->id,
                            'parent_message_id' => $message->id,
                            'message' => fake()->realText(rand(20, 100)),
                            'created_at' => fake()->dateTimeBetween($message->created_at, 'now'),
                        ]);
                    }
                }
            }
        }
    }
}
