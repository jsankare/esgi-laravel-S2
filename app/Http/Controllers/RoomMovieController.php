<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Movie;
use App\Services\OmdbService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoomMovieController extends Controller
{
    protected $omdbService;

    public function __construct(OmdbService $omdbService)
    {
        $this->omdbService = $omdbService;
    }

    public function search(Request $request, Room $room)
    {
        $search = $request->get('search');

        if (empty($search)) {
            return response()->json(['movies' => []]);
        }

        // Get both local and OMDB results
        $localMovies = Movie::search($search)->take(10)->get();
        $omdbResults = Cache::remember(
            'omdb_search_' . md5($search),
            now()->addHours(24),
            fn() => $this->omdbService->searchMovies($search)
        );

        // Combine and deduplicate results
        $allMovies = collect();

        // Add local movies first
        $localMovies->each(function($movie) use ($allMovies) {
            $allMovies->push([
                'Title' => $movie->title,
                'Year' => $movie->year,
                'imdbID' => $movie->imdb_id,
                'Poster' => $movie->poster_url,
                'Director' => $movie->director,
                'Genre' => $movie->genre,
                'Plot' => $movie->plot
            ]);
        });

        // Add OMDB movies, excluding any that are already in local results
        if (isset($omdbResults['movies']) && $omdbResults['movies']->isNotEmpty()) {
            $omdbResults['movies']->each(function($movie) use ($allMovies, $localMovies) {
                if (!$localMovies->contains('imdb_id', $movie['imdbID'])) {
                    $allMovies->push($movie);
                }
            });
        }

        return response()->json([
            'movies' => $allMovies->take(10)
        ]);
    }

    public function store(Request $request, Room $room)
    {
        // Check if user is member of the room
        if (!$room->users->contains(auth()->id())) {
            return response()->json(['error' => 'You must be a member to add movies.'], 403);
        }

        // Check if user has already added 5 movies
        $userMoviesCount = $room->movies()
            ->wherePivot('user_id', auth()->id())
            ->count();

        if ($userMoviesCount >= 5) {
            return response()->json(['error' => 'You can only add up to 5 movies.'], 422);
        }

        // Check if movie already exists in the room
        $movieExists = $room->movies()
            ->where('imdb_id', $request->imdb_id)
            ->exists();

        if ($movieExists) {
            return response()->json(['error' => 'This movie has already been added to the room.'], 422);
        }

        // Get movie details from cache or OMDB
        $cacheKey = 'movie_details_' . $request->imdb_id;
        $movieDetails = Cache::remember($cacheKey, now()->addDays(7), function () use ($request) {
            return $this->omdbService->getMovieDetails($request->imdb_id);
        });

        if (!$movieDetails) {
            return response()->json(['error' => 'Movie not found.'], 404);
        }

        // Create or update movie record
        $movie = Movie::updateOrCreate(
            ['imdb_id' => $movieDetails['imdbID']],
            [
                'title' => $movieDetails['Title'],
                'year' => (int) filter_var($movieDetails['Year'], FILTER_SANITIZE_NUMBER_INT),
                'genre' => $movieDetails['Genre'],
                'director' => $movieDetails['Director'],
                'plot' => $movieDetails['Plot'],
                'poster_url' => $movieDetails['Poster'] !== 'N/A' ? $movieDetails['Poster'] : null,
            ]
        );

        // Attach movie to room with user_id
        $room->movies()->attach($movie->id, ['user_id' => auth()->id()]);

        return response()->json([
            'message' => 'Movie added successfully.',
            'movie' => $movie
        ]);
    }

    public function destroy(Room $room, Movie $movie)
    {
        // Check if the movie was added by the current user
        $isUserMovie = $room->movies()
            ->wherePivot('user_id', auth()->id())
            ->wherePivot('movie_id', $movie->id)
            ->exists();

        if (!$isUserMovie) {
            return response()->json(['error' => 'You can only remove your own movies.'], 403);
        }

        $room->movies()->detach($movie->id);
        return response()->json(['message' => 'Movie removed successfully.']);
    }
}
