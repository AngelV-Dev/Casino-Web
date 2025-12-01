<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ========== ARCHIVO 2: app/Listeners/UnlockWelcomeAchievement.php ==========

namespace App\Listeners;

use App\Events\UserCreated;

class UnlockWelcomeAchievement
{
    public function handle(UserCreated $event): void
    {
        $event->user->unlockAchievement(
            'account_created',
            '🎉 ¡Bienvenido al Casino!',
            'Creaste tu cuenta',
            'welcome.png'
        );
    }
}