<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register a dynamic route for testing the middleware
        Route::middleware(['web', 'permission:test-permission'])
            ->get('/_test/permission-guarded', function () {
                return 'accessed';
            });
    }

    /**
     * Test that an unauthenticated user is redirected to the login page.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/_test/permission-guarded');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Please log in to access this page.');
    }

    /**
     * Test that an authenticated user without the required permission is redirected to the admin dashboard.
     */
    public function test_authenticated_user_without_permission_is_redirected_to_dashboard(): void
    {
        // Create a user
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/_test/permission-guarded');

        // It should redirect to admin.dashboard, NOT to route('login') or '/'
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error', 'Unauthorized. Permission required: test-permission');
    }

    /**
     * Test that an authenticated user with the required permission is allowed to proceed.
     */
    public function test_authenticated_user_with_permission_can_access(): void
    {
        // Create role and permission
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'is_active' => true,
            'level' => 50,
        ]);

        $permission = new Permission([
            'name' => 'Test Permission',
            'slug' => 'test-permission',
            'module' => 'test',
            'is_active' => true,
        ]);
        $permission->category = 'test';
        $permission->save();

        $role->permissions()->attach($permission->id);

        // Create user and attach role
        $user = User::factory()->create([
            'role' => 'admin',
        ]);
        $user->roles()->attach($role->id);

        $response = $this->actingAs($user)->get('/_test/permission-guarded');

        $response->assertStatus(200);
        $response->assertSee('accessed');
    }

    /**
     * Test that a super admin is always allowed access, even without explicit permission.
     */
    public function test_super_admin_is_always_allowed_access(): void
    {
        // Create a super admin user
        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($user)->get('/_test/permission-guarded');

        $response->assertStatus(200);
        $response->assertSee('accessed');
    }

    /**
     * Test that the default admin role gets service.create permission and can access the guarded route.
     */
    public function test_admin_role_gets_default_service_permissions(): void
    {
        // Seed default permissions and roles
        Permission::createDefaultPermissions();
        Role::createDefaultRoles();
        Permission::assignDefaultPermissions();

        // Create an admin user (will automatically trigger role attachment in boot)
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        // Verify the user has the service.create permission
        $this->assertTrue($user->hasPermission('service.create'));

        // Register a temporary route with service.create permission
        Route::middleware(['web', 'permission:service.create'])
            ->get('/_test/service-create-guarded', function () {
                return 'accessed';
            });

        // Verify the user can access this route
        $response = $this->actingAs($user)->get('/_test/service-create-guarded');
        $response->assertStatus(200);
        $response->assertSee('accessed');
    }
}
