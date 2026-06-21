<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberImportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default roles and permissions
        Permission::createDefaultPermissions();
        Role::createDefaultRoles();
        Permission::assignDefaultPermissions();

        // Ensure member.create permission is created and assigned to admin role
        $permission = Permission::firstOrCreate(
            ['slug' => 'member.create'],
            [
                'name' => 'Create Members',
                'module' => 'members',
                'is_active' => true
            ]
        );

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $church = \App\Models\Church::create([
            'name' => 'Test Church',
            'type' => 'adult',
            'description' => 'Test Church Description',
        ]);

        $this->user = User::factory()->create([
            'role' => 'admin',
            'church_id' => $church->id,
        ]);
    }

    /**
     * Test that the CSV template download works and contains correct columns.
     */
    public function test_csv_template_download(): void
    {
        $response = $this->actingAs($this->user)->get(route('members.import.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $lines = explode("\n", trim($content));
        
        $this->assertNotEmpty($lines);
        $headers = str_getcsv($lines[0]);

        $expectedHeaders = [
            'EMAIL',
            'SURNAME',
            'FIRSTNAME',
            'OTHER NAME',
            'DAY OF BIRTH',
            'MONTH OF BIRTH',
            'GENDER',
            'EMERGENCY CONTACT NAME & PHONE NUMBER',
            'MARITAL STATUS',
            'NAME OF PARTNER (if married)',
            'PHONE NUMBER (primary)',
            'STATE OF ORIGIN',
            'LOCAL GOVERNMENT',
            'STATE OF RESIDENCE',
            'CITY OF RESIDENCE',
            'STREET NAME & NUMBER',
            'PROFESSION/OCCUPATION',
            'GROUP IN CHURCH',
            'DEPARTMENT IN CHURCH',
            'BAPTIZED',
            'LOCATION & YEAR OF BAPTISM',
            'CHURCH OF BAPTISM',
            'SPIRITUAL GIFTS'
        ];

        $this->assertEquals($expectedHeaders, $headers);
    }

    /**
     * Test that the Excel template download works.
     */
    public function test_excel_template_download(): void
    {
        $response = $this->actingAs($this->user)->get(route('members.import.template.excel'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=member_import_template.xlsx');
    }

    /**
     * Test that importing a valid CSV succeeds.
     */
    public function test_import_valid_csv(): void
    {
        $headers = [
            'EMAIL', 'SURNAME', 'FIRSTNAME', 'OTHER NAME', 'DAY OF BIRTH', 'MONTH OF BIRTH',
            'GENDER', 'EMERGENCY CONTACT NAME & PHONE NUMBER', 'MARITAL STATUS', 'NAME OF PARTNER (if married)',
            'PHONE NUMBER (primary)', 'STATE OF ORIGIN', 'LOCAL GOVERNMENT', 'STATE OF RESIDENCE',
            'CITY OF RESIDENCE', 'STREET NAME & NUMBER', 'PROFESSION/OCCUPATION', 'GROUP IN CHURCH',
            'DEPARTMENT IN CHURCH', 'BAPTIZED', 'LOCATION & YEAR OF BAPTISM', 'CHURCH OF BAPTISM', 'SPIRITUAL GIFTS'
        ];

        $row = [
            'test.member@example.com', 'Smith', 'Jane', 'Marie', '25', 'March',
            'FEMALE', 'John Smith: 08011111111', 'MARRIED', 'John Smith',
            '08022222222', 'Enugu', 'Nsukka', 'Lagos',
            'Ikeja', '45 Allen Ave, Ikeja', 'Manager', 'Women Fellowship',
            'USHERING', 'YES', '2015 - Enugu', 'Methodist', 'Prophecy'
        ];

        // Write sample data to a temporary stream
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers);
        fputcsv($stream, $row);
        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        // Create virtual file
        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        $response = $this->actingAs($this->user)->post(route('members.import'), [
            'import_file' => $file,
            'skip_duplicates' => true,
            'update_existing' => false
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Check that the member was successfully created in the database
        $member = Member::withoutGlobalScopes()->where('email', 'test.member@example.com')->first();
        $this->assertNotNull($member);
        $this->assertEquals('Smith', $member->last_name);
        $this->assertEquals('Jane', $member->first_name);
        $this->assertEquals('Marie', $member->other_names);
        $this->assertEquals('25', $member->birth_day);
        $this->assertEquals('March', $member->birth_month);
        $this->assertEquals('female', $member->gender);
        $this->assertEquals('John Smith: 08011111111', $member->emergency_contact_details);
        $this->assertEquals('MARRIED', $member->marital_status);
        $this->assertEquals('John Smith', $member->partner_name);
        $this->assertEquals('08022222222', $member->phone);
        $this->assertEquals('Enugu', $member->state_of_origin);
        $this->assertEquals('Nsukka', $member->lga_of_origin);
        $this->assertEquals('Lagos', $member->state_of_residence);
        $this->assertEquals('Ikeja', $member->city_of_residence);
        $this->assertEquals('45 Allen Ave, Ikeja', $member->address);
        $this->assertEquals('Manager', $member->profession);
        $this->assertEquals('Women Fellowship', $member->church_group);
        $this->assertEquals('YES', $member->is_baptized);
        $this->assertEquals('2015 - Enugu', $member->baptism_year_and_place);
        $this->assertEquals('Methodist', $member->baptism_church_name);
        $this->assertEquals('Prophecy', $member->spiritual_gifts);

        // Verify that the department was assigned
        $this->assertTrue(
            $member->departments()->whereHas('department', function ($query) {
                $query->where('name', 'USHERING');
            })->exists()
        );
    }

    /**
     * Test CSV preview returns correct JSON response.
     */
    public function test_preview_csv(): void
    {
        $headers = [
            'EMAIL', 'SURNAME', 'FIRSTNAME', 'OTHER NAME', 'DAY OF BIRTH', 'MONTH OF BIRTH',
            'GENDER', 'EMERGENCY CONTACT NAME & PHONE NUMBER', 'MARITAL STATUS', 'NAME OF PARTNER (if married)',
            'PHONE NUMBER (primary)', 'STATE OF ORIGIN', 'LOCAL GOVERNMENT', 'STATE OF RESIDENCE',
            'CITY OF RESIDENCE', 'STREET NAME & NUMBER', 'PROFESSION/OCCUPATION', 'GROUP IN CHURCH',
            'DEPARTMENT IN CHURCH', 'BAPTIZED', 'LOCATION & YEAR OF BAPTISM', 'CHURCH OF BAPTISM', 'SPIRITUAL GIFTS'
        ];

        $row = [
            'preview.member@example.com', 'Jones', 'Bob', 'Edward', '10', 'August',
            'MALE', 'Alex Jones: 08033333333', 'SINGLE', '',
            '08044444444', 'Kano', 'Kano Municipal', 'Kano',
            'Kano', '12 Sabon Gari', 'Merchant', 'Men Fellowship',
            'CHOIR', 'NO', '', '', 'Faith'
        ];

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers);
        fputcsv($stream, $row);
        rewind($stream);
        $csvContent = stream_get_contents($stream);
        fclose($stream);

        $file = UploadedFile::fake()->createWithContent('import_preview.csv', $csvContent);

        $response = $this->actingAs($this->user)->post(route('members.import.preview'), [
            'import_file' => $file
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_rows' => 1
        ]);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['headers']);
        
        // Assert some key headers are parsed as expected
        $this->assertContains('email', $responseData['headers']);
        $this->assertContains('surname', $responseData['headers']);
        $this->assertContains('firstname', $responseData['headers']);
        $this->assertContains('other_name', $responseData['headers']);
        $this->assertContains('day_of_birth', $responseData['headers']);
    }

    /**
     * Test that guest users see Portal Login on the landing page.
     */
    public function test_guest_sees_portal_login_on_landing_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Portal Login');
        $response->assertDontSee('Dashboard');
    }

    /**
     * Test that authenticated users see Dashboard on the landing page.
     */
    public function test_authenticated_user_sees_dashboard_on_landing_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertDontSee('Portal Login');
    }

    /**
     * Test that member export contains the exact same 23 headings as the import template.
     */
    public function test_export_contains_exact_import_template_headers(): void
    {
        // Add member.view permission to user role (needed to trigger export)
        $permission = Permission::firstOrCreate(
            ['slug' => 'member.view'],
            [
                'name' => 'View Members',
                'module' => 'members',
                'is_active' => true
            ]
        );

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Trigger CSV export
        $response = $this->actingAs($this->user)->get(route('members.export.csv'));

        $response->assertStatus(200);
        $filePath = $response->getFile()->getPathname();
        $content = file_get_contents($filePath);
        $lines = explode("\n", trim($content));
        
        $this->assertNotEmpty($lines);
        $headers = str_getcsv($lines[0]);

        $expectedHeaders = [
            'EMAIL',
            'SURNAME',
            'FIRSTNAME',
            'OTHER NAME',
            'DAY OF BIRTH',
            'MONTH OF BIRTH',
            'GENDER',
            'EMERGENCY CONTACT NAME & PHONE NUMBER',
            'MARITAL STATUS',
            'NAME OF PARTNER (if married)',
            'PHONE NUMBER (primary)',
            'STATE OF ORIGIN',
            'LOCAL GOVERNMENT',
            'STATE OF RESIDENCE',
            'CITY OF RESIDENCE',
            'STREET NAME & NUMBER',
            'PROFESSION/OCCUPATION',
            'GROUP IN CHURCH',
            'DEPARTMENT IN CHURCH',
            'BAPTIZED',
            'LOCATION & YEAR OF BAPTISM',
            'CHURCH OF BAPTISM',
            'SPIRITUAL GIFTS'
        ];

        $this->assertEquals($expectedHeaders, $headers);
    }

    /**
     * Test that bulk deleting selected members by ID works correctly.
     */
    public function test_bulk_delete_selected_members(): void
    {
        // Ensure member.delete permission is created and assigned
        $permission = Permission::firstOrCreate(
            ['slug' => 'member.delete'],
            [
                'name' => 'Delete Members',
                'module' => 'members',
                'is_active' => true
            ]
        );

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Create some members using forceCreate to set church_id
        $member1 = Member::forceCreate([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'm1@example.com',
            'phone' => '08011111111',
            'address' => '123 Main St',
            'gender' => 'male',
            'membership_status' => 'active',
            'church_id' => $this->user->church_id,
            'unique_id' => 'MEM-2026-1001',
        ]);
        $member2 = Member::forceCreate([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'm2@example.com',
            'phone' => '08022222222',
            'address' => '456 Oak Ave',
            'gender' => 'female',
            'membership_status' => 'active',
            'church_id' => $this->user->church_id,
            'unique_id' => 'MEM-2026-1002',
        ]);
        $member3 = Member::forceCreate([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'm3@example.com',
            'phone' => '08033333333',
            'address' => '789 Pine St',
            'gender' => 'male',
            'membership_status' => 'inactive',
            'church_id' => $this->user->church_id,
            'unique_id' => 'MEM-2026-1003',
        ]);

        // Call bulk delete for m1 and m2
        $response = $this->actingAs($this->user)->post(route('members.bulk-delete'), [
            'delete_type' => 'selected',
            'member_ids' => [$member1->id, $member2->id]
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Assert member 1 and 2 are soft deleted, but 3 remains
        $this->assertNotNull(Member::withoutGlobalScopes()->onlyTrashed()->find($member1->id));
        $this->assertNotNull(Member::withoutGlobalScopes()->onlyTrashed()->find($member2->id));
        $this->assertNotNull(Member::withoutGlobalScopes()->whereNull('deleted_at')->find($member3->id));
    }

    /**
     * Test that bulk deleting members matching active filters works correctly.
     */
    public function test_bulk_delete_filtered_members(): void
    {
        // Ensure member.delete permission is created and assigned
        $permission = Permission::firstOrCreate(
            ['slug' => 'member.delete'],
            [
                'name' => 'Delete Members',
                'module' => 'members',
                'is_active' => true
            ]
        );

        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        // Create some members with status using forceCreate
        $activeMember = Member::forceCreate([
            'first_name' => 'John',
            'last_name' => 'Active',
            'email' => 'active@example.com',
            'phone' => '08011111111',
            'address' => '123 Main St',
            'gender' => 'male',
            'membership_status' => 'active',
            'church_id' => $this->user->church_id,
            'unique_id' => 'MEM-2026-2001',
        ]);
        $inactiveMember1 = Member::forceCreate([
            'first_name' => 'Jane',
            'last_name' => 'Inactive',
            'email' => 'inactive1@example.com',
            'phone' => '08022222222',
            'address' => '456 Oak Ave',
            'gender' => 'female',
            'membership_status' => 'inactive',
            'church_id' => $this->user->church_id,
            'unique_id' => 'MEM-2026-2002',
        ]);
        $inactiveMember2 = Member::forceCreate([
            'first_name' => 'Bob',
            'last_name' => 'Inactive',
            'email' => 'inactive2@example.com',
            'phone' => '08033333333',
            'address' => '789 Pine St',
            'gender' => 'male',
            'membership_status' => 'inactive',
            'church_id' => $this->user->church_id,
            'unique_id' => 'MEM-2026-2003',
        ]);

        // Call bulk delete for filter status = 'inactive'
        $response = $this->actingAs($this->user)->post(route('members.bulk-delete'), [
            'delete_type' => 'filtered',
            'status' => 'inactive'
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Assert inactive members are deleted, but active one remains
        $this->assertNotNull(Member::withoutGlobalScopes()->onlyTrashed()->find($inactiveMember1->id));
        $this->assertNotNull(Member::withoutGlobalScopes()->onlyTrashed()->find($inactiveMember2->id));
        $this->assertNotNull(Member::withoutGlobalScopes()->whereNull('deleted_at')->find($activeMember->id));
    }

    /**
     * Test that creating a youth church member succeeds without the baptism field.
     */
    public function test_create_youth_church_member_succeeds_without_baptism(): void
    {
        // Change our user's church type to youth
        $this->user->church->update(['type' => 'youth']);

        $response = $this->actingAs($this->user)->post(route('members.store'), [
            'first_name' => 'Youth',
            'last_name' => 'Member',
            'email' => 'youth.member@example.com',
            'phone' => '08099999999',
            'address' => '789 Youth St',
            'birth_day' => '12',
            'birth_month' => 'September',
            'gender' => 'female',
            'marital_status' => 'SINGLE',
            'state_of_origin' => 'Lagos',
            'lga_of_origin' => 'Ikeja',
            'state_of_residence' => 'Lagos',
            'city_of_residence' => 'Ikeja',
            'profession' => 'Student',
            'departments' => ['CHOIR']
            // 'is_baptized' is omitted!
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $member = Member::withoutGlobalScopes()->where('email', 'youth.member@example.com')->first();
        $this->assertNotNull($member);
        $this->assertNull($member->is_baptized);
    }
}
