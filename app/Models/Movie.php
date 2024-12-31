<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'imdb_id',
        'year',
        'genre',
        'director',
        'plot',
        'poster_url',
        'eliminated_by',
        'eliminated_at',
    ];

    /**
     * Get the user who eliminated the movie.
     */
    public function eliminatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eliminated_by');
    }

    /**
     * Get the rooms where this movie exists.
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_movie')
            ->withPivot('user_id')
            ->withTimestamps();
    }
}
