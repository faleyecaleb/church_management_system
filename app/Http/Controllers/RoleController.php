<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['create', 'store']);
        $this->middleware('permission:roles.update')->only(['edit', 'update', 'syncPermissions']);
        $this->middleware('permission:roles.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Role::withCount('members');

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

        $roles = $query->orderBy('level')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::active()->orderBy('module')->get();
        $modules = Permission::getModules();

        return view('roles.create', compact('permissions', 'modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();

        try {
            $role = Role::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'level' => $validated['level'],
                'is_active' => $validated['is_active'] ?? true
            ]);

            $role->permissions()->attach($validated['permissions']);

            DB::commit();

            return redirect()->route('roles.show', $role)
                ->with('success', 'Role created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function show(Role $role)
    {
        $role->load(['permissions', 'members']);
        $stats = $role->getRoleStatistics();

        return view('roles.show', compact('role', 'stats'));
    }

    public function edit(Role $role)
    {
        if ($role->isSystem()) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be edited.');
        }

        $permissions = Permission::active()->orderBy('module')->get();
        $modules = Permission::getModules();
        $role->load('permissions');

        return view('roles.edit', compact('role', 'permissions', 'modules'));
    }

    public function update(Request $request, Role $role)
    {
        if ($role->isSystem()) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be updated.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();

        try {
            $role->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'level' => $validated['level'],
                'is_active' => $validated['is_active'] ?? true
            ]);

            $role->permissions()->sync($validated['permissions']);

            DB::commit();

            return redirect()->route('roles.show', $role)
                ->with('success', 'Role updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        if ($role->isSystem()) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        if ($role->members()->exists()) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete role that has assigned members.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function syncPermissions(Request $request, Role $role)
    {
        if ($role->isSystem()) {
            return redirect()->route('roles.index')
                ->with('error', 'System roles cannot be modified.');
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->permissions()->sync($validated['permissions']);

        return redirect()->route('roles.show', $role)
            ->with('success', 'Role permissions updated successfully.');
    }

    public function createDefault()
    {
        try {
            Role::createDefaultRoles();
            return redirect()->route('roles.index')
                ->with('success', 'Default roles created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('roles.index')
                ->with('error', 'Failed to create default roles: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $roles = Role::with('permissions:id,name')
            ->get()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'description' => $role->description,
                    'level' => $role->level,
                    'is_active' => $role->is_active,
                    'permissions' => $role->permissions->pluck('name')->toArray()
                ];
            });

        $filename = 'roles_' . now()->format('Y-m-d_His') . '.json';

        return response()->json($roles)
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

            foreach ($content as $roleData) {
                try {
                    // Find or create role
                    $role = Role::firstOrNew(['name' => $roleData['name']]);
                    $role->fill([
                        'description' => $roleData['description'],
                        'level' => $roleData['level'],
                        'is_active' => $roleData['is_active'] ?? true,
                        'slug' => Str::slug($roleData['name'])
                    ]);
                    $role->save();

                    // Sync permissions
                    $permissions = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
                    $role->permissions()->sync($permissions);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to import role '{$roleData['name']}': " . 
                        $e->getMessage();
                }
            }

            DB::commit();

            $message = $imported . ' roles imported successfully.';
            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(' ', $errors);
                $type = 'warning';
            } else {
                $type = 'success';
            }

            return redirect()->route('roles.index')
                ->with($type, $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('roles.index')
                ->with('error', 'Failed to import roles: ' . $e->getMessage());
        }
    }
}