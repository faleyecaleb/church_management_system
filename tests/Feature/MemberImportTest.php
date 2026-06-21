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
}
