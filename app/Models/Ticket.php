<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at', 'asc');
    }

    // Métodos útiles
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function close()
    {
        $this->update(['status' => 'closed']);
    }

    public function reopen()
    {
        $this->update(['status' => 'open']);
    }

    public function markInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'open' => ['text' => 'Abierto', 'color' => 'green'],
            'in_progress' => ['text' => 'En Progreso', 'color' => 'yellow'],
            'closed' => ['text' => 'Cerrado', 'color' => 'gray'],
            default => ['text' => 'Desconocido', 'color' => 'gray'],
        };
    }
}

