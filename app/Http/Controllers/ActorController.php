<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TmdbService;

class ActorController extends Controller
{

    protected $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    public function show($id)
    {
        $actor = $this->tmdbService->getActor($id);

        if (!$actor) {
            abort(404);
        }

        return view('actors.show', [
            'actor' => $actor,
            'movies' => $movies['cast'] ?? [],
        ]);
    }
}
