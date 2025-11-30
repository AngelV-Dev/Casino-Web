<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteGame extends Model
{
    protected $fillable = ['user_id', 'game_key', 'game_name', 'game_image', 'hours_played', 'games_played', 'games_won'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
