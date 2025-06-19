<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar_url',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the audit logs associated with the user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the roles associated with the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the permissions associated with the user through roles.
     */
    public function permissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            Role::class,
            'user_id',
            'role_id',
            'id',
            'id'
        );
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('slug', $permission);
        })->exists();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            if ($user->isAdmin()) {
                $adminRole = Role::findBySlug(Role::ADMIN);
                if ($adminRole) {
                    $user->roles()->attach($adminRole->id);
                    $adminRole->permissions()->sync(
                        Permission::where('module', '!=', Permission::MODULE_SETTINGS)
                            ->where('slug', '!=', 'role.manage')
                            ->pluck('id')
                    );
                }
            }
        });

        static::updated(function ($user) {
            if ($user->isAdmin() && $user->isDirty('role')) {
                $adminRole = Role::findBySlug(Role::ADMIN);
                if ($adminRole) {
                    $user->roles()->sync([$adminRole->id]);
                    $adminRole->permissions()->sync(
                        Permission::where('module', '!=', Permission::MODULE_SETTINGS)
                            ->where('slug', '!=', 'role.manage')
                            ->pluck('id')
                    );
                }
            }
        });
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->isAdmin() || $this->permissions()->whereIn('name', $permissions)->exists();
    }

    /**
     * Check if the user has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $userPermissions = $this->permissions()->whereIn('name', $permissions)->pluck('name');
        return count($permissions) === $userPermissions->count();
    }

    /**
     * Get the internal messages sent by the user.
     */
    public function sentMessages()
    {
        return $this->hasMany(InternalMessage::class, 'sender_id');
    }

    /**
     * Get the internal messages received by the user.
     */
    public function receivedMessages()
    {
        return $this->belongsToMany(InternalMessage::class, 'internal_message_recipients', 'user_id', 'message_id')
            ->withPivot(['read_at'])
            ->withTimestamps();
    }

    /**
     * Get the message groups associated with the user.
     */
    public function messageGroups()
    {
        return $this->belongsToMany(MessageGroup::class);
    }
}
