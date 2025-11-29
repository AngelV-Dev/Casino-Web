<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiceBet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bet_amount',
        'target_number',
        'direction',
        'result_number',
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
        'target_number' => 'decimal:2',
        'result_number' => 'decimal:2',
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