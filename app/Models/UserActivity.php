<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $fillable = ['user_id', 'activity_type', 'game_name', 'description', 'amount', 'won', 'happened_at'];
    protected $casts = ['happened_at' => 'datetime'];
    protected $table = 'user_activities';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
