<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Member;
use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduling_admin_birthday_notifications()
    {
        // 1. Create an admin user
        $admin = User::forceCreate([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 2. Create a member with a birthday in exactly 14 days
        $birthdayIn14Days = now()->addDays(14);
        $member = Member::forceCreate([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => bcrypt('password'),
            'date_of_birth' => $birthdayIn14Days->format('Y-m-d'),
        ]);

        $service = new NotificationService();
        $service->scheduleAdminBirthdayNotifications();

        // 3. Assert that the "Upcoming Member Birthday" notification was scheduled
        $this->assertTrue(
            Notification::where('recipient_id', $admin->id)
                ->where('recipient_type', User::class)
                ->where('type', Notification::TYPE_ADMIN_BIRTHDAY_REMINDER)
                ->where('data->member_id', $member->id)
                ->where('data->reminder_type', 'week_before')
                ->exists()
        );
    }

    public function test_processing_admin_birthday_due_notifications()
    {
        // Fake Expo Push API
        Http::fake([
            'https://exp.host/--/api/v2/push/send' => Http::response(['data' => ['status' => 'ok']]),
        ]);

        // 1. Create an admin user with push token
        $admin = User::forceCreate([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'expo_push_token' => 'ExponentPushToken[12345]',
        ]);

        // 2. Create a scheduled due notification
        $notification = Notification::forceCreate([
            'type' => Notification::TYPE_ADMIN_BIRTHDAY_REMINDER,
            'title' => 'Upcoming Member Birthday',
            'message' => 'Upcoming birthday for John Doe in a week',
            'recipient_id' => $admin->id,
            'recipient_type' => User::class,
            'data' => [
                'member_id' => 1,
                'birthday_date' => now()->addDays(7)->format('Y-m-d'),
                'reminder_type' => 'week_before',
            ],
            'status' => Notification::STATUS_SCHEDULED,
            'scheduled_at' => now()->subMinute(),
        ]);

        $service = new NotificationService();
        $service->processDueNotifications();

        // 3. Assert that notification is now sent
        $notification->refresh();
        $this->assertEquals(Notification::STATUS_SENT, $notification->status);

        // 4. Assert that Expo Push Notification API was called
        Http::assertSent(function ($request) {
            return $request->url() === 'https://exp.host/--/api/v2/push/send' &&
                $request['to'] === 'ExponentPushToken[12345]' &&
                $request['title'] === 'Upcoming Member Birthday';
        });
    }

    public function test_birthday_board_page_access_for_super_admin()
    {
        $superAdmin = User::forceCreate([
            'name' => 'Senior Pastor',
            'email' => 'senior_pastor@hosanna',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('members.birthdays'));

        $response->assertStatus(200);
        $response->assertViewIs('members.birthdays');
    }

    public function test_birthday_board_page_displays_correct_birthdays()
    {
        $superAdmin = User::forceCreate([
            'name' => 'Senior Pastor',
            'email' => 'senior_pastor@hosanna',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        // Create members with birthdays today, tomorrow, and yesterday
        $todayMember = Member::forceCreate([
            'first_name' => 'Today',
            'last_name' => 'Celebrant',
            'email' => 'today@example.com',
            'password' => bcrypt('password'),
            'date_of_birth' => now()->format('Y-m-d'),
        ]);

        $tomorrowMember = Member::forceCreate([
            'first_name' => 'Tomorrow',
            'last_name' => 'Celebrant',
            'email' => 'tomorrow@example.com',
            'password' => bcrypt('password'),
            'date_of_birth' => now()->addDay()->format('Y-m-d'),
        ]);

        $yesterdayMember = Member::forceCreate([
            'first_name' => 'Yesterday',
            'last_name' => 'Celebrant',
            'email' => 'yesterday@example.com',
            'password' => bcrypt('password'),
            'date_of_birth' => now()->subDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('members.birthdays'));

        $response->assertStatus(200);
        $response->assertSee('Today Celebrant');
        $response->assertSee('Tomorrow Celebrant');
        $response->assertSee('Yesterday Celebrant');
    }
}
