<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Predefined modules
    const MODULE_MEMBER = 'member';
    const MODULE_ATTENDANCE = 'attendance';
    const MODULE_FINANCE = 'finance';
    const MODULE_COMMUNICATION = 'communication';
    const MODULE_EQUIPMENT = 'equipment';
    const MODULE_SETTINGS = 'settings';
    const MODULE_ROLE = 'role';
    const MODULE_AUDIT = 'audit';

    // Predefined actions
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_MANAGE = 'manage';
    const ACTION_APPROVE = 'approve';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('slug', 'LIKE', "%{$action}");
    }

    // Helper methods
    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }

    public static function getModules()
    {
        return [
            self::MODULE_MEMBER => 'Member Management',
            self::MODULE_ATTENDANCE => 'Attendance Management',
            self::MODULE_FINANCE => 'Financial Management',
            self::MODULE_COMMUNICATION => 'Communication',
            self::MODULE_EQUIPMENT => 'Equipment Management',
            self::MODULE_SETTINGS => 'System Settings',
            self::MODULE_ROLE => 'Role Management',
            self::MODULE_AUDIT => 'Audit Logs'
        ];
    }

    public static function getActions()
    {
        return [
            self::ACTION_VIEW => 'View',
            self::ACTION_CREATE => 'Create',
            self::ACTION_UPDATE => 'Update',
            self::ACTION_DELETE => 'Delete',
            self::ACTION_MANAGE => 'Manage',
            self::ACTION_APPROVE => 'Approve',
            self::ACTION_EXPORT => 'Export',
            self::ACTION_IMPORT => 'Import'
        ];
    }

    public static function getDefaultPermissions()
    {
        $permissions = [];
        $modules = self::getModules();
        $actions = self::getActions();

        foreach ($modules as $moduleSlug => $moduleName) {
            foreach ($actions as $actionSlug => $actionName) {
                $permissions[] = [
                    'name' => "{$actionName} {$moduleName}",
                    'slug' => "{$moduleSlug}.{$actionSlug}",
                    'description' => "Ability to {$actionSlug} in {$moduleName}",
                    'module' => $moduleSlug,
                    'is_active' => true
                ];
            }
        }

        // Add special permissions
        $permissions[] = [
            'name' => 'Access Dashboard',
            'slug' => 'dashboard.access',
            'description' => 'Ability to access the dashboard',
            'module' => 'dashboard',
            'is_active' => true
        ];

        $permissions[] = [
            'name' => 'Generate Reports',
            'slug' => 'reports.generate',
            'description' => 'Ability to generate system reports',
            'module' => 'reports',
            'is_active' => true
        ];

        return $permissions;
    }

    public static function createDefaultPermissions()
    {
        foreach (self::getDefaultPermissions() as $permission) {
            self::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }

    public static function assignDefaultPermissions()
    {
        // Super Admin gets all permissions
        $superAdmin = Role::findBySlug(Role::SUPER_ADMIN);
        if ($superAdmin) {
            $superAdmin->permissions()->sync(self::all());
        }

        // Admin gets most permissions except some sensitive ones
        $admin = Role::findBySlug(Role::ADMIN);
        if ($admin) {
            $admin->permissions()->sync(
                self::where('module', '!=', self::MODULE_SETTINGS)
                    ->where('slug', '!=', 'role.manage')
                    ->get()
            );
        }

        // Pastor gets member and communication related permissions
        $pastor = Role::findBySlug(Role::PASTOR);
        if ($pastor) {
            $pastor->permissions()->sync(
                self::whereIn('module', [self::MODULE_MEMBER, self::MODULE_COMMUNICATION])
                    ->orWhere('slug', 'dashboard.access')
                    ->get()
            );
        }

        // Staff gets basic operational permissions
        $staff = Role::findBySlug(Role::STAFF);
        if ($staff) {
            $staff->permissions()->sync(
                self::whereIn('module', [self::MODULE_ATTENDANCE, self::MODULE_EQUIPMENT])
                    ->orWhere('slug', 'dashboard.access')
                    ->orWhere('slug', 'member.view')
                    ->get()
            );
        }

        // Regular members get view-only permissions
        $member = Role::findBySlug(Role::MEMBER);
        if ($member) {
            $member->permissions()->sync(
                self::where('slug', 'LIKE', '%.view')
                    ->orWhere('slug', 'dashboard.access')
                    ->get()
            );
        }

        // Guests get minimal permissions
        $guest = Role::findBySlug(Role::GUEST);
        if ($guest) {
            $guest->permissions()->sync(
                self::whereIn('slug', ['dashboard.access'])
                    ->get()
            );
        }
    }

    public static function getPermissionStats()
    {
        return [
            'total_permissions' => self::count(),
            'active_permissions' => self::active()->count(),
            'permissions_by_module' => self::selectRaw('module, COUNT(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module'),
            'roles_by_permission' => self::withCount('roles')
                ->get()
                ->pluck('roles_count', 'name'),
            'most_used_permissions' => self::withCount('roles')
                ->orderByDesc('roles_count')
                ->limit(10)
                ->get()
                ->map(function ($permission) {
                    return [
                        'name' => $permission->name,
                        'usage_count' => $permission->roles_count
                    ];
                })
        ];
    }
}