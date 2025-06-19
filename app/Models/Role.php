<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'level'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer'
    ];

    // Predefined roles
    const SUPER_ADMIN = 'super_admin';
    const ADMIN = 'admin';
    const PASTOR = 'pastor';
    const STAFF = 'staff';
    const MEMBER = 'member';
    const GUEST = 'guest';

    // Relationships
    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_role');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    // Helper methods
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }

        return $this->permissions()->where('id', $permission->id)->exists();
    }

    public function hasPermissions($permissions)
    {
        if (empty($permissions)) {
            return true;
        }

        $permissionSlugs = collect($permissions)->map(function ($permission) {
            return is_string($permission) ? $permission : $permission->slug;
        });

        return $this->permissions()
            ->whereIn('slug', $permissionSlugs)
            ->count() === count($permissions);
    }

    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        if (!$this->hasPermission($permission)) {
            $this->permissions()->attach($permission->id);

            // Log the change
            AuditLog::log(
                AuditLog::PERMISSION_GRANTED,
                auth()->id(),
                self::class,
                $this->id,
                [],
                ['permission' => $permission->slug],
                ['role', 'permission']
            );
        }
    }

    public function revokePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        if ($this->hasPermission($permission)) {
            $this->permissions()->detach($permission->id);

            // Log the change
            AuditLog::log(
                AuditLog::PERMISSION_REVOKED,
                auth()->id(),
                self::class,
                $this->id,
                ['permission' => $permission->slug],
                [],
                ['role', 'permission']
            );
        }
    }

    public function syncPermissions($permissions)
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            return is_string($permission)
                ? Permission::where('slug', $permission)->firstOrFail()->id
                : $permission->id;
        });

        $this->permissions()->sync($permissionIds);

        // Log the change
        AuditLog::log(
            AuditLog::PERMISSION_UPDATED,
            auth()->id(),
            self::class,
            $this->id,
            ['permissions' => $this->permissions->pluck('slug')->toArray()],
            ['permissions' => Permission::whereIn('id', $permissionIds)->pluck('slug')->toArray()],
            ['role', 'permission']
        );
    }

    public static function getDefaultRoles()
    {
        return [
            self::SUPER_ADMIN => [
                'name' => 'Super Administrator',
                'level' => 100,
                'description' => 'Has complete system access'
            ],
            self::ADMIN => [
                'name' => 'Administrator',
                'level' => 90,
                'description' => 'Has administrative access with some restrictions'
            ],
            self::PASTOR => [
                'name' => 'Pastor',
                'level' => 80,
                'description' => 'Has access to spiritual and member management features'
            ],
            self::STAFF => [
                'name' => 'Staff Member',
                'level' => 70,
                'description' => 'Has access to day-to-day operational features'
            ],
            self::MEMBER => [
                'name' => 'Church Member',
                'level' => 10,
                'description' => 'Has basic member access'
            ],
            self::GUEST => [
                'name' => 'Guest',
                'level' => 1,
                'description' => 'Has limited access to public features'
            ]
        ];
    }

    public static function createDefaultRoles()
    {
        foreach (self::getDefaultRoles() as $slug => $role) {
            self::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'level' => $role['level'],
                    'is_active' => true
                ]
            );
        }
    }

    public function canManage($role)
    {
        if (is_string($role)) {
            $role = self::where('slug', $role)->first();
        }

        return $role && $this->level > $role->level;
    }

    public static function getRoleStats()
    {
        return [
            'total_roles' => self::count(),
            'active_roles' => self::active()->count(),
            'roles_by_level' => self::selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level'),
            'members_by_role' => self::withCount('members')
                ->get()
                ->pluck('members_count', 'name'),
            'permissions_by_role' => self::withCount('permissions')
                ->get()
                ->pluck('permissions_count', 'name')
        ];
    }
}