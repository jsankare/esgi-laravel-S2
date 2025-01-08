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
        'elimination_started',
        'elimination_in_progress',
        'last_elimination_at',
    ];

    protected $casts = [
        'elimination_started' => 'boolean',
        'elimination_in_progress' => 'boolean',
        'last_elimination_at' => 'datetime',
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

    /**
     * Check if elimination can be started
     */
    public function canStartElimination(): bool
    {
        return !$this->elimination_started &&
            !$this->elimination_in_progress &&
            $this->movies()->count() > 1;
    }
}
