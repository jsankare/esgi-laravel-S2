<?php

namespace App\Http\Controllers;

use App\Services\OmdbService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    protected $omdbService;

    public function __construct(OmdbService $omdbService)
    {
        $this->omdbService = $omdbService;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $genre = $request->get('genre');
        $page = $request->get('page');

        $result = $this->omdbService->searchMovies($search, $genre, $page);
        $genres = $this->omdbService->getAllGenres();

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
        $movie = $this->omdbService->getMovieDetails($imdbId);

        if (!$movie) {
            abort(404);
        }

        return view('movies.show', ['movie' => $movie]);
    }
}
