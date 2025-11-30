<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotBet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bet_amount',
        'lines',
        'total_bet',
        'result',
        'winning_lines',
        'multiplier',
        'payout',
        'profit',
        'is_win',
        'server_seed',
        'client_seed',
        'nonce'
    ];

    protected $casts = [
        'bet_amount' => 'decimal:2',
        'total_bet' => 'decimal:2',
        'result' => 'array',
        'winning_lines' => 'array',
        'multiplier' => 'decimal:2',
        'payout' => 'decimal:2',
        'profit' => 'decimal:2',
        'is_win' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}