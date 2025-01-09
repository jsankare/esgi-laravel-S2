<?php

namespace Database\Seeders;

use App\Models\MovieReaction;
use App\Models\Room;
use Illuminate\Database\Seeder;

class MovieReactionSeeder extends Seeder
{
    public function run(): void
    {
        $emojis = ['👍', '👎', '❤️', '😂', '😮', '😡'];

        $rooms = Room::with(['users', 'movies'])->get();

        foreach ($rooms as $room) {
            // Skip if room has no users or movies
            if ($room->users->isEmpty() || $room->movies->isEmpty()) {
                continue;
            }

            foreach ($room->movies as $movie) {
                // For each movie, 30-70% of room members will react
                $numberOfReactions = rand(
                    (int)($room->users->count() * 0.3),
                    (int)($room->users->count() * 0.7)
                );

                // Get random users from the room
                $reactingUsers = $room->users->random($numberOfReactions);

                foreach ($reactingUsers as $user) {
                    MovieReaction::create([
                        'user_id' => $user->id,
                        'room_id' => $room->id,
                        'movie_id' => $movie->id,
                        'emoji' => $emojis[array_rand($emojis)],
                        'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
                    ]);
                }
            }
        }
    }
}
