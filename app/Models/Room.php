<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    protected $fillable = [
        'name',
        'password',
        'creator_id',
    ];

    /**
     * Get the creator of the room.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the movies in this room.
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'room_movie')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    /**
     * Get the users associated with this room.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'room_user')
            ->withTimestamps();
    }
}
