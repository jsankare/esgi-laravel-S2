<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.themoviedb.org/3';
    protected string $imageBaseUrl = 'https://image.tmdb.org/t/p/original';

    public function __construct()
    {
        $this->apiKey = env('TMDB_API_KEY');
    }

    public function searchMovies(?string $search = '', ?string $genre = '', ?int $page = 1, ?int $limit = 18): array
    {
        $query = [
            'api_key' => $this->apiKey,
            'page' => $page ?? 1,
            'include_adult' => false,
            'language' => 'en-US'
        ];

        if (!empty($search)) {
            $query['query'] = $search;
            $response = Http::get($this->baseUrl . '/search/movie', $query);
        } else {
            // Default to popular movies if no search term
            $response = Http::get($this->baseUrl . '/movie/popular', $query);
        }

        $data = $response->json();

        if ($response->successful() && isset($data['results'])) {
            $movies = collect($data['results'])->map(function ($movie) use ($genre) {
                $details = $this->getMovieDetails($movie['id']);
                if (!$genre || (isset($details['genres']) && collect($details['genres'])->pluck('name')->contains($genre))) {
                    return [
                        'imdbID' => $details['imdb_id'] ?? '',
                        'Title' => $movie['title'],
                        'Year' => substr($movie['release_date'] ?? '', 0, 4),
                        'Genre' => collect($details['genres'] ?? [])->pluck('name')->implode(', '),
                        'Director' => $this->getDirector($details['credits'] ?? []),
                        'Plot' => $movie['overview'],
                        'Poster' => $movie['poster_path'] ? $this->imageBaseUrl . $movie['poster_path'] : null,
                        'Ratings' => [
                            [
                                'Source' => 'TMDb',
                                'Value' => $movie['vote_average'] . '/10'
                            ]
                        ]
                    ];
                }
                return null;
            })->filter();

            if ($limit) {
                $movies = $movies->take($limit);
            }

            return [
                'movies' => $movies,
                'total' => $data['total_results'] ?? 0,
                'current_page' => $data['page'] ?? 1,
            ];
        }

        return [
            'movies' => collect(),
            'total' => 0,
            'current_page' => 1
        ];
    }

    public function getMovieDetails(string $id): ?array
    {
        $response = Http::get($this->baseUrl . "/movie/{$id}", [
            'api_key' => $this->apiKey,
            'append_to_response' => 'credits',
            'language' => 'en-US'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'imdb_id' => $data['imdb_id'] ?? '',
                'Title' => $data['title'],
                'Year' => substr($data['release_date'] ?? '', 0, 4),
                'Rated' => $data['adult'] ? 'R' : 'PG-13',
                'Runtime' => $data['runtime'] . ' min',
                'Genre' => collect($data['genres'])->pluck('name')->implode(', '),
                'Director' => $this->getDirector($data['credits'] ?? []),
                'Writer' => $this->getWriters($data['credits'] ?? []),
                'Actors' => $this->getActors($data['credits'] ?? []),
                'Plot' => $data['overview'],
                'Poster' => $data['poster_path'] ? $this->imageBaseUrl . $data['poster_path'] : null,
                'Ratings' => [
                    [
                        'Source' => 'TMDb',
                        'Value' => $data['vote_average'] . '/10'
                    ]
                ],
                'genres' => $data['genres'],
                'credits' => $data['credits']
            ];
        }

        return null;
    }

    public function getAllGenres(): array
    {
        $response = Http::get($this->baseUrl . '/genre/movie/list', [
            'api_key' => $this->apiKey,
            'language' => 'en-US'
        ]);

        if ($response->successful()) {
            return collect($response->json()['genres'])->pluck('name')->toArray();
        }

        return [];
    }

    protected function getDirector(array $credits): string
    {
        return collect($credits['crew'] ?? [])
            ->where('job', 'Director')
            ->pluck('name')
            ->implode(', ') ?: 'N/A';
    }

    protected function getWriters(array $credits): string
    {
        return collect($credits['crew'] ?? [])
            ->whereIn('job', ['Screenplay', 'Writer', 'Story'])
            ->pluck('name')
            ->unique()
            ->implode(', ') ?: 'N/A';
    }

    protected function getActors(array $credits): string
    {
        return collect($credits['cast'] ?? [])
            ->take(5)
            ->pluck('name')
            ->implode(', ') ?: 'N/A';
    }
}
