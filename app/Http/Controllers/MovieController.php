<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    /**
     * Display a listing of the movies.
     */
    public function index()
    {
        $movies = Movie::all();
        return response()->json($movies);
    }

    /**
     * Store a newly created movie in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'imdb_id' => 'nullable|string|max:255',
            'year' => 'required|integer',
            'genre' => 'nullable|string|max:255',
            'director' => 'nullable|string|max:255',
            'plot' => 'nullable|string',
            'poster_url' => 'nullable|string|max:255',
        ]);

        $movie = Movie::create($validated);

        return response()->json($movie, 201);
    }

    /**
     * Display the specified movie.
     */
    public function show(Movie $movie)
    {
        return response()->json($movie);
    }

    /**
     * Update the specified movie in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'imdb_id' => 'nullable|string|max:255',
            'year' => 'sometimes|integer',
            'genre' => 'nullable|string|max:255',
            'director' => 'nullable|string|max:255',
            'plot' => 'nullable|string',
            'poster_url' => 'nullable|string|max:255',
        ]);

        $movie->update($validated);

        return response()->json($movie);
    }

    /**
     * Remove the specified movie from storage.
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();

        return response()->json(['message' => 'Movie deleted successfully.']);
    }
}
