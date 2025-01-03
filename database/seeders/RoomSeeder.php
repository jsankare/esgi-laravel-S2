<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use App\Models\Movie;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $movies = Movie::all();

        // Ensure there are enough users and movies to proceed
        if ($users->count() < 2 || $movies->count() < 2) {
            $this->command->warn('Not enough users or movies to seed rooms.');
            return;
        }

        // Create rooms
        for ($i = 1; $i <= 5; $i++) {
            $creator = $users->random();

            $room = Room::create([
                'name' => "Movie Room $i",
                'creator_id' => $creator->id,
                'password' => $i % 2 === 0 ? bcrypt('password') : null, // Every other room has a password
            ]);

            // Add creator to room members
            $room->users()->attach($creator->id);

            // Add 2-3 random members to each room
            $maxMembers = min($users->except($creator->id)->count(), 3); // Ensure no more than available
            $members = $users->except($creator->id)->random(rand(2, $maxMembers));
            foreach ($members as $member) {
                $room->users()->attach($member->id);
            }

            // Add 2-4 random movies to each room
            $maxMovies = min($movies->count(), 4); // Ensure no more than available
            $roomMovies = $movies->random(rand(2, $maxMovies));
            foreach ($roomMovies as $movie) {
                $room->movies()->attach($movie->id, [
                    'user_id' => $room->users->random()->id
                ]);
            }
        }
    }
}
