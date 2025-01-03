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

        // Create 5 rooms
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
            $members = $users->except($creator->id)->random(rand(2, 3));
            foreach ($members as $member) {
                $room->users()->attach($member->id);
            }

            // Add 2-4 random movies to each room
            $roomMovies = $movies->random(rand(2, 4));
            foreach ($roomMovies as $movie) {
                $room->movies()->attach($movie->id, [
                    'user_id' => $room->users->random()->id
                ]);
            }
        }
    }
}
