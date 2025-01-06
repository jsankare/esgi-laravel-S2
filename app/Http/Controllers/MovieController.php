<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $genre = $request->get('genre');
        $page = $request->get('page');

        $result = $this->tmdbService->searchMovies($search, $genre, $page);
        $genres = $this->tmdbService->getAllGenres();

        return view('movies.index', [
            'movies' => $result['movies'],
            'total' => $result['total'],
            'currentPage' => $result['current_page'],
            'genres' => $genres,
            'selectedGenre' => $genre,
            'search' => $search,
        ]);
    }

    public function show(string $imdbId)
    {
        $movie = $this->tmdbService->getMovieDetails($imdbId);

        if (!$movie) {
            abort(404);
        }

        return view('movies.show', ['movie' => $movie]);
    }
}
