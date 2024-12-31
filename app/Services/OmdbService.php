<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OmdbService
{
    protected string $apiKey;
    protected string $baseUrl = 'http://www.omdbapi.com/';

    public function __construct()
    {
        $this->apiKey = env('OMDB_API_KEY');
    }

    public function searchMovies(?string $search = '', ?string $genre = '', ?int $page = 1)
    {
        $query = ['apikey' => $this->apiKey, 'page' => $page ?? 1, 'type' => 'movie'];

        if (!empty($search)) {
            $query['s'] = $search;
        } else {
            // If no search term, use a default search to get movies
            $query['s'] = 'movie';
        }

        $response = Http::get($this->baseUrl, $query);
        $data = $response->json();

        if ($response->successful() && isset($data['Search'])) {
            $movies = collect($data['Search'])->map(function ($movie) use ($genre) {
                $details = $this->getMovieDetails($movie['imdbID']);
                if (!$genre || (isset($details['Genre']) && str_contains($details['Genre'], $genre))) {
                    return $details;
                }
                return null;
            })->filter();

            return [
                'movies' => $movies,
                'total' => (int) ($data['totalResults'] ?? 0),
                'current_page' => $page ?? 1,
            ];
        }

        return ['movies' => collect(), 'total' => 0, 'current_page' => 1];
    }

    public function getMovieDetails(string $imdbId)
    {
        $response = Http::get($this->baseUrl, [
            'apikey' => $this->apiKey,
            'i' => $imdbId,
            'plot' => 'full'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getAllGenres()
    {
        return [
            'Action', 'Adventure', 'Animation', 'Biography', 'Comedy',
            'Crime', 'Documentary', 'Drama', 'Family', 'Fantasy',
            'Film-Noir', 'History', 'Horror', 'Music', 'Musical',
            'Mystery', 'Romance', 'Sci-Fi', 'Sport', 'Thriller',
            'War', 'Western'
        ];
    }
}
