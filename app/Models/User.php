<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Transaction;
use App\Models\GameSession;
use App\Models\Wallet;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'suspension_reason',
        'suspended_at',
        'avatar',
        'banner',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_at' => 'datetime',
        ];
    }

    // ==================== MÉTODOS DE ROLES ====================
    
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isModerator()
    {
        return $this->role === 'moderator';
    }

    public function isSupport()
    {
        return $this->role === 'support';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles)
    {
        return in_array($this->role, $roles);
    }

    // ==================== RELACIONES ====================

    /**
     * Perfil del usuario
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Wallet del usuario
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Transacciones
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Sesiones de juego
     */
    public function gameSessions()
    {
        return $this->hasMany(GameSession::class);
    }

    // ==================== SISTEMA DE PERMISOS ====================
    
    public static function getRolePermissions()
    {
        return [
            'super_admin' => [
                'manage_admins',
                'manage_moderators',
                'manage_support',
                'manage_users',
                'change_any_role',
                'delete_any_admin',
                'suspend_users',
                'ban_users',
                'view_all_tickets',
                'manage_games',
                'view_reports',
                'manage_transactions',
                'access_settings',
            ],
            
            'admin' => [
                'manage_moderators',
                'manage_support',
                'manage_users',
                'change_user_role',
                'suspend_users',
                'ban_users',
                'view_all_tickets',
                'respond_tickets',
                'view_reports',
                'manage_transactions',
            ],
            
            'moderator' => [
                'view_users',
                'suspend_users',
                'view_user_details',
                'view_tickets',
                'respond_tickets',
                'view_reports',
            ],
            
            'support' => [
                'view_tickets',
                'respond_tickets',
                'close_tickets',
                'view_user_details',
            ],
            
            'user' => [
                'play_games',
                'create_tickets',
                'update_profile',
                'add_friends',
                'view_rankings',
            ],
        ];
    }

    public function hasPermission($permission)
    {
        $permissions = self::getRolePermissions()[$this->role] ?? [];
        return in_array($permission, $permissions);
    }

    public function hasAnyPermission(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    public function canModify(User $targetUser)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isAdmin()) {
            return in_array($targetUser->role, ['moderator', 'support', 'user']);
        }

        if ($this->isModerator()) {
            return $targetUser->isUser() && $this->can('suspend_users');
        }

        return false;
    }

    public function canChangeRoleTo(User $targetUser, $newRole)
    {
        if (in_array($newRole, ['super_admin', 'admin'])) {
            return $this->isSuperAdmin();
        }

        if (in_array($newRole, ['moderator', 'support', 'user'])) {
            return $this->isAdmin() || $this->isSuperAdmin();
        }

        return false;
    }

    public function canDelete(User $targetUser)
    {
        if ($this->id === $targetUser->id) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return !$targetUser->isSuperAdmin();
        }

        if ($this->isAdmin()) {
            return in_array($targetUser->role, ['user', 'moderator', 'support']);
        }

        return false;
    }

    // ==================== MÉTODOS DE ESTADO ====================
    
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function isBanned()
    {
        return $this->status === 'banned';
    }

    public function suspend($reason)
    {
        $this->update([
            'status' => 'suspended',
            'suspension_reason' => $reason,
            'suspended_at' => now(),
        ]);
    }

    public function ban($reason)
    {
        $this->update([
            'status' => 'banned',
            'suspension_reason' => $reason,
            'suspended_at' => now(),
        ]);
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);
    }

    // ==================== AVATAR ====================

    public function getAvatarUrlAttribute()
    {
        return $this->avatar
            ? asset('avatars/' . $this->avatar)
            : asset('avatars/default.png');
    }

    // =================== FRIENDS ===================
    public function friends()
    {
        return $this->hasMany(Friend::class, 'user_id');
    }

    public function friendOf()
    {
        return $this->hasMany(Friend::class, 'friend_id');
    }

    public function achievements()
    {
    return $this->hasMany(UserAchievement::class);
    }

    public function favoriteGame()
    {
        return $this->hasOne(UserFavoriteGame::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class)->orderBy('happened_at', 'desc');
    }

    // ===== MÉTODO: Desbloquear Logro =====
    public function unlockAchievement($achievementKey, $title, $description, $icon)
    {
        // Verificar si ya lo tiene desbloqueado
        $existing = $this->achievements()->where('achievement_key', $achievementKey)->first();
        
        if ($existing) {
            return $existing; // Ya existe
        }

    // Crear nuevo logro
    return $this->achievements()->create([
        'achievement_key' => $achievementKey,
        'title' => $title,
        'description' => $description,
        'icon' => $icon,
        'unlocked_at' => now()
    ]);
    }

// ===== MÉTODO: Registrar Actividad =====
public function recordActivity($activityType, $description, $gameName = null, $amount = null, $won = false)
{
    return $this->activities()->create([
        'activity_type' => $activityType,
        'game_name' => $gameName,
        'description' => $description,
        'amount' => $amount,
        'won' => $won,
        'happened_at' => now()
    ]);
}

// ===== MÉTODO: Establecer Juego Favorito =====
public function setFavoriteGame($gameKey, $gameName, $gameImage)
{
    // Si ya existe, actualizar
    if ($this->favoriteGame) {
        $this->favoriteGame()->update([
            'game_key' => $gameKey,
            'game_name' => $gameName,
            'game_image' => $gameImage
        ]);
        return $this->favoriteGame;
    }

    // Si no existe, crear
    return $this->favoriteGame()->create([
        'game_key' => $gameKey,
        'game_name' => $gameName,
        'game_image' => $gameImage,
        'hours_played' => 0,
        'games_played' => 0,
        'games_won' => 0
    ]);
    }
}
