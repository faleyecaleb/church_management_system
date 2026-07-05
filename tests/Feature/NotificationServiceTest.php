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
}
