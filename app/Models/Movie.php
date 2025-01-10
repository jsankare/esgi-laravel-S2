<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Movie extends Model
{
    use Searchable;
    use HasFactory;

    protected $fillable = [
        'title',
        'imdb_id',
        'year',
        'genre',
        'director',
        'plot',
        'poster_url',
    ];

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

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_movie')
            ->withPivot(['user_id', 'eliminated_by', 'eliminated_at'])
            ->withTimestamps();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MovieReaction::class);
    }

}
