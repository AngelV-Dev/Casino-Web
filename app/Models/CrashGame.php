<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrashGame extends Model
{
    protected $fillable = [
        'user_id',
        'bet_amount',
        'crash_point',
        'cashout_at',
        'profit',
        'won'
    ];
}
