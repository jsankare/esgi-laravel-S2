<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieReaction extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'movie_id',
        'emoji'
    ];

    // List of supported emojis that work across all systems
    public static $supportedEmojis = [
        '👍', '👎', '❤️', '😂', '😮', '😢', '🤔', '🎬'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class);
    }
}
