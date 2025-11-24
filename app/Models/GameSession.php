<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'game_id',
        'bet_amount',
        'win_amount',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
