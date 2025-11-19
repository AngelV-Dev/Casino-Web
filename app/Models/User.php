<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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


    // ==================== SISTEMA DE PERMISOS ====================
    
    /**
     * Permisos definidos por rol
     */
    public static function getRolePermissions()
    {
        return [
            'super_admin' => [
                // Control total del sistema
                'manage_admins',           // Crear/eliminar otros admins
                'manage_moderators',       // Crear/eliminar moderadores
                'manage_support',          // Crear/eliminar soporte
                'manage_users',            // CRUD de usuarios normales
                'change_any_role',         // Cambiar rol de cualquiera
                'delete_any_admin',        // Eliminar otros admins
                'suspend_users',
                'ban_users',
                'view_all_tickets',
                'manage_games',
                'view_reports',
                'manage_transactions',
                'access_settings',
            ],
            
            'admin' => [
                // Administración limitada (NO puede tocar super_admin ni otros admins)
                'manage_moderators',       // Puede crear moderadores
                'manage_support',          // Puede crear soporte
                'manage_users',            // CRUD de usuarios normales
                'change_user_role',        // Solo cambiar rol de users (no admins)
                'suspend_users',
                'ban_users',
                'view_all_tickets',
                'respond_tickets',
                'view_reports',
                'manage_transactions',
            ],
            
            'moderator' => [
                // Moderación de usuarios y contenido
                'view_users',              // Ver lista de usuarios
                'suspend_users',           // Suspender usuarios (NO banear)
                'view_user_details',       // Ver detalles de usuarios
                'view_tickets',            // Ver tickets
                'respond_tickets',         // Responder tickets
                'view_reports',            // Ver reportes limitados
            ],
            
            'support' => [
                // Solo soporte técnico
                'view_tickets',            // Ver tickets asignados
                'respond_tickets',         // Responder tickets
                'close_tickets',           // Cerrar tickets
                'view_user_details',       // Ver info básica de usuarios
            ],
            
            'user' => [
                // Usuario normal
                'play_games',
                'create_tickets',
                'update_profile',
                'add_friends',
                'view_rankings',
            ],
        ];
    }

    /**
     * Verificar si el usuario tiene un permiso
     */
    /**
     * Verificar si el usuario tiene un permiso (custom)
     */
    public function hasPermission($permission)
    {
        $permissions = self::getRolePermissions()[$this->role] ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Verificar si tiene alguno de los permisos (custom)
     */
    public function hasAnyPermission(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si tiene todos los permisos (custom)
     */
    public function hasAllPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }


    /**
     * Verificar si puede modificar a otro usuario
     */
    public function canModify(User $targetUser)
    {
        // Super admin puede modificar a todos (excepto a sí mismo en algunas acciones)
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin puede modificar a moderators, support y users (NO a admins ni super_admins)
        if ($this->isAdmin()) {
            return in_array($targetUser->role, ['moderator', 'support', 'user']);
        }

        // Moderador NO puede modificar a nadie directamente
        // Solo puede suspender (con permiso específico)
        if ($this->isModerator()) {
            return $targetUser->isUser() && $this->can('suspend_users');
        }

        return false;
    }

    /**
     * Verificar si puede cambiar el rol de otro usuario
     */
    public function canChangeRoleTo(User $targetUser, $newRole)
    {
        // Solo super_admin puede cambiar roles a admin o super_admin
        if (in_array($newRole, ['super_admin', 'admin'])) {
            return $this->isSuperAdmin();
        }

        // Admin puede asignar moderator o support
        if (in_array($newRole, ['moderator', 'support', 'user'])) {
            return $this->isAdmin() || $this->isSuperAdmin();
        }

        return false;
    }

    /**
     * Verificar si puede eliminar a otro usuario
     */
    public function canDelete(User $targetUser)
    {
        // Nadie puede eliminarse a sí mismo
        if ($this->id === $targetUser->id) {
            return false;
        }

        // Super admin puede eliminar a todos excepto otros super_admins
        if ($this->isSuperAdmin()) {
            return !$targetUser->isSuperAdmin();
        }

        // Admin solo puede eliminar users, moderators y support
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
}

