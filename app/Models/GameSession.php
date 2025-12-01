<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    use HasFactory;

    // ESTA ES LA PARTE IMPORTANTE QUE PROBABLEMENTE TE FALTA:
    protected $fillable = [
        'user_id',
        'game_id',
        'bet_amount',
        'win_amount',
        'result',      // <--- Permiso para guardar si ganaste o perdiste
        'game_data',   // <--- Permiso para guardar dónde están las bombas
        'is_active',
    ];

    protected $casts = [
        'game_data' => 'array', // Esto convierte el JSON a Array automáticamente
        'bet_amount' => 'decimal:2',
        'win_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}