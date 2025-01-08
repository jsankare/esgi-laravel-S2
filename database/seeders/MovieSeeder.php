<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    public function run(): void
    {
        $movies = [
            [
                'title' => 'The Shawshank Redemption',
                'imdb_id' => 'tt0111161',
                'year' => 1994,
                'genre' => 'Drama',
                'director' => 'Frank Darabont',
                'plot' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.',
                'poster_url' => 'https://m.media-amazon.com/images/M/MV5BMDFkYTc0MGEtZmNhMC00ZDIzLWFmNTEtODM1ZmRlYWMwMWFmXkEyXkFqcGdeQXVyMTMxODk2OTU@._V1_SX300.jpg',
            ],
            [
                'title' => 'The Godfather',
                'imdb_id' => 'tt0068646',
                'year' => 1972,
                'genre' => 'Crime, Drama',
                'director' => 'Francis Ford Coppola',
                'plot' => 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.',
                'poster_url' => 'https://m.media-amazon.com/images/M/MV5BM2MyNjYxNmUtYTAwNi00MTYxLWJmNWYtYzZlODY3ZTk3OTFlXkEyXkFqcGdeQXVyNzkwMjQ5NzM@._V1_SX300.jpg',
            ],
            [
                'title' => 'Pulp Fiction',
                'imdb_id' => 'tt0110912',
                'year' => 1994,
                'genre' => 'Crime, Drama',
                'director' => 'Quentin Tarantino',
                'plot' => 'The lives of two mob hitmen, a boxer, a gangster and his wife, and a pair of diner bandits intertwine in four tales of violence and redemption.',
                'poster_url' => 'https://m.media-amazon.com/images/M/MV5BNGNhMDIzZTUtNTBlZi00MTRlLWFjM2ItYzViMjE3YzI5MjljXkEyXkFqcGdeQXVyNzkwMjQ5NzM@._V1_SX300.jpg',
            ],
        ];

        foreach ($movies as $movie) {
            Movie::create($movie);
        }
    }
}
