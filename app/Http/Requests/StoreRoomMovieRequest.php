<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomMovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'imdb_id' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'imdb_id.required' => 'Movie ID is required',
            'imdb_id.max' => 'Invalid movie ID format',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $room = $this->route('room');

            // Check if user is member of the room
            if (!$room->users->contains(auth()->id())) {
                $validator->errors()->add('user', 'You must be a member to add movies.');
            }

            // Check if user has already added 5 movies
            $userMoviesCount = $room->movies()
                ->wherePivot('user_id', auth()->id())
                ->count();

            if ($userMoviesCount >= 5) {
                $validator->errors()->add('movies', 'You can only add up to 5 movies.');
            }

            // Check if movie already exists in the room
            $movieExists = $room->movies()
                ->where('imdb_id', $this->imdb_id)
                ->exists();

            if ($movieExists) {
                $validator->errors()->add('movie', 'This movie has already been added to the room.');
            }
        });
    }
}
