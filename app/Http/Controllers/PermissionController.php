<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:permissions.view')->only(['index', 'show']);
        $this->middleware('permission:permissions.create')->only(['create', 'store']);
        $this->middleware('permission:permissions.update')->only(['edit', 'update']);
        $this->middleware('permission:permissions.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $permissions = $query->orderBy('module')->orderBy('name')->paginate(20);
        $modules = Permission::getModules();

        return view('permissions.index', compact('permissions', 'modules'));
    }

    public function create()
    {
        $modules = Permission::getModules();
        $actions = Permission::getActions();
        return view('permissions.create', compact('modules', 'actions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'description' => 'nullable|string|max:1000',
            'module' => 'required|string|max:50',
            'action' => 'required|string|max:50',
            'is_active' => 'boolean'
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'module' => $validated['module'],
            'action' => $validated['action'],
            'is_active' => $validated['is_active'] ?? true
        ]);

        return redirect()->route('permissions.show', $permission)
            ->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        $permission->load('roles');
        $stats = $permission->getPermissionStatistics();

        return view('permissions.show', compact('permission', 'stats'));
    }

    public function edit(Permission $permission)
    {
        if ($permission->isSystem()) {
            return redirect()->route('permissions.index')
                ->with('error', 'System permissions cannot be edited.');
        }

        $modules = Permission::getModules();
        $actions = Permission::getActions();

        return view('permissions.edit', compact('permission', 'modules', 'actions'));
    }

    public function update(Request $request, Permission $permission)
    {
        if ($permission->isSystem()) {
            return redirect()->route('permissions.index')
                ->with('error', 'System permissions cannot be updated.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'description' => 'nullable|string|max:1000',
            'module' => 'required|string|max:50',
            'action' => 'required|string|max:50',
            'is_active' => 'boolean'
        ]);

        $permission->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'module' => $validated['module'],
            'action' => $validated['action'],
            'is_active' => $validated['is_active'] ?? true
        ]);

        return redirect()->route('permissions.show', $permission)
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        if ($permission->isSystem()) {
            return redirect()->route('permissions.index')
                ->with('error', 'System permissions cannot be deleted.');
        }

        if ($permission->roles()->exists()) {
            return redirect()->route('permissions.index')
                ->with('error', 'Cannot delete permission that is assigned to roles.');
        }

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    public function createDefault()
    {
        try {
            Permission::createDefaultPermissions();
            return redirect()->route('permissions.index')
                ->with('success', 'Default permissions created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('permissions.index')
                ->with('error', 'Failed to create default permissions: ' . $e->getMessage());
        }
    }

    public function assignDefaultToRoles()
    {
        try {
            Permission::assignDefaultPermissionsToRoles();
            return redirect()->route('permissions.index')
                ->with('success', 'Default permissions assigned to roles successfully.');
        } catch (\Exception $e) {
            return redirect()->route('permissions.index')
                ->with('error', 'Failed to assign default permissions: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $permissions = Permission::with('roles:id,name')
            ->get()
            ->map(function ($permission) {
                return [
                    'name' => $permission->name,
                    'description' => $permission->description,
                    'module' => $permission->module,
                    'action' => $permission->action,
                    'is_active' => $permission->is_active,
                    'roles' => $permission->roles->pluck('name')->toArray()
                ];
            });

        $filename = 'permissions_' . now()->format('Y-m-d_His') . '.json';

        return response()->json($permissions)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048'
        ]);

        try {
            $content = json_decode(file_get_contents($request->file('file')), true);

            if (!is_array($content)) {
                throw new \Exception('Invalid file format');
            }

            DB::beginTransaction();

            $imported = 0;
            $errors = [];

            foreach ($content as $permissionData) {
                try {
                    // Find or create permission
                    $permission = Permission::firstOrNew(['name' => $permissionData['name']]);
                    $permission->fill([
                        'description' => $permissionData['description'],
                        'module' => $permissionData['module'],
                        'action' => $permissionData['action'],
                        'is_active' => $permissionData['is_active'] ?? true,
                        'slug' => Str::slug($permissionData['name'])
                    ]);
                    $permission->save();

                    // Sync roles if provided
                    if (isset($permissionData['roles'])) {
                        $roles = Role::whereIn('name', $permissionData['roles'])->pluck('id');
                        $permission->roles()->sync($roles);
                    }

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to import permission '{$permissionData['name']}': " . 
                        $e->getMessage();
                }
            }

            DB::commit();

            $message = $imported . ' permissions imported successfully.';
            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(' ', $errors);
                $type = 'warning';
            } else {
                $type = 'success';
            }

            return redirect()->route('permissions.index')
                ->with($type, $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('permissions.index')
                ->with('error', 'Failed to import permissions: ' . $e->getMessage());
        }
    }
}