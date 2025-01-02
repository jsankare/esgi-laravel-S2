<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class Movie extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'imdb_id',
        'year',
        'genre',
        'director',
        'plot',
        'poster_url',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'director' => $this->director,
            'genre' => $this->genre,
            'year' => $this->year,
            'plot' => $this->plot,
        ];
    }

    /**
     * Get the rooms where this movie exists.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_movie')
            ->withPivot(['user_id', 'eliminated_by', 'eliminated_at'])
            ->withTimestamps();
    }
}
