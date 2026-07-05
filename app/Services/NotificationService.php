<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Schedule birthday notifications for all members.
     */
    public function scheduleBirthdayNotifications()
    {
        $members = Member::whereNotNull('date_of_birth')->get();

        foreach ($members as $member) {
            $nextBirthday = Carbon::parse($member->date_of_birth)
                ->setYear(now()->year);

            if ($nextBirthday->isPast()) {
                $nextBirthday->addYear();
            }

            // Schedule notification 2 days before birthday
            $scheduleDate = $nextBirthday->copy()->subDays(2);

            if ($scheduleDate->isFuture()) {
                Notification::create([
                    'type' => Notification::TYPE_BIRTHDAY,
                    'title' => 'Birthday Reminder',
                    'message' => "Upcoming birthday for {$member->name} on {$nextBirthday->format('M d')}",
                    'recipient_id' => $member->id,
                    'recipient_type' => Member::class,
                    'data' => [
                        'birthday_date' => $nextBirthday->format('Y-m-d'),
                        'age' => $nextBirthday->diffInYears($member->date_of_birth),
                    ],
                    'status' => Notification::STATUS_SCHEDULED,
                    'scheduled_at' => $scheduleDate,
                ]);
            }
        }
    }

    /**
     * Schedule anniversary notifications for all members.
     */
    public function scheduleAnniversaryNotifications()
    {
        $members = Member::whereNotNull('membership_date')->get();

        foreach ($members as $member) {
            $nextAnniversary = Carbon::parse($member->membership_date)
                ->setYear(now()->year);

            if ($nextAnniversary->isPast()) {
                $nextAnniversary->addYear();
            }

            // Schedule notification 3 days before anniversary
            $scheduleDate = $nextAnniversary->copy()->subDays(3);

            if ($scheduleDate->isFuture()) {
                $years = $nextAnniversary->diffInYears($member->membership_date);

                Notification::create([
                    'type' => Notification::TYPE_ANNIVERSARY,
                    'title' => 'Membership Anniversary',
                    'message' => "Celebrating {$years} years of membership for {$member->name} on {$nextAnniversary->format('M d')}",
                    'recipient_id' => $member->id,
                    'recipient_type' => Member::class,
                    'data' => [
                        'anniversary_date' => $nextAnniversary->format('Y-m-d'),
                        'years' => $years,
                    ],
                    'status' => Notification::STATUS_SCHEDULED,
                    'scheduled_at' => $scheduleDate,
                ]);
            }
        }
    }

    /**
     * Schedule milestone notifications based on membership duration.
     */
    public function scheduleMilestoneNotifications()
    {
        $milestones = [1, 5, 10, 15, 20, 25, 30]; // Years of membership

        $members = Member::whereNotNull('membership_date')
            ->where('membership_date', '<=', now())
            ->get();

        foreach ($members as $member) {
            $membershipYears = Carbon::parse($member->membership_date)->diffInYears(now());

            foreach ($milestones as $milestone) {
                if ($membershipYears == $milestone) {
                    Notification::create([
                        'type' => Notification::TYPE_MILESTONE,
                        'title' => 'Membership Milestone',
                        'message' => "Congratulations to {$member->name} for {$milestone} years of faithful membership!",
                        'recipient_id' => $member->id,
                        'recipient_type' => Member::class,
                        'data' => [
                            'milestone_years' => $milestone,
                            'membership_date' => $member->membership_date,
                        ],
                        'status' => Notification::STATUS_PENDING,
                    ]);
                    break;
                }
            }
        }
    }

    /**
     * Create a follow-up notification for a member.
     */
    public function createFollowUpNotification(Member $member, $reason, $dueDate = null)
    {
        return Notification::create([
            'type' => Notification::TYPE_FOLLOWUP,
            'title' => 'Member Follow-up Required',
            'message' => "Follow-up required for {$member->name}: {$reason}",
            'recipient_id' => $member->id,
            'recipient_type' => Member::class,
            'data' => [
                'reason' => $reason,
                'member_phone' => $member->phone,
                'member_email' => $member->email,
            ],
            'status' => $dueDate ? Notification::STATUS_SCHEDULED : Notification::STATUS_PENDING,
            'scheduled_at' => $dueDate,
        ]);
    }

    /**
     * Create a custom notification for a member.
     */
    public function createCustomNotification(Member $member, $title, $message, $scheduledAt = null, $additionalData = [])
    {
        return Notification::create([
            'type' => Notification::TYPE_CUSTOM,
            'title' => $title,
            'message' => $message,
            'recipient_id' => $member->id,
            'recipient_type' => Member::class,
            'data' => $additionalData,
            'status' => $scheduledAt ? Notification::STATUS_SCHEDULED : Notification::STATUS_PENDING,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    /**
     * Process due notifications.
     */
    public function processDueNotifications()
    {
        $dueNotifications = Notification::due()->get();

        foreach ($dueNotifications as $notification) {
            try {
                $recipient = $notification->recipient;

                if ($notification->type === Notification::TYPE_BIRTHDAY) {
                    // Send email
                    if ($recipient && $recipient->email) {
                        Mail::to($recipient->email)->send(new \App\Mail\BirthdayWish($recipient, $notification->data['age']));
                    }

                    // Send SMS
                    if ($recipient && $recipient->phone) {
                        $smsMessage = new \App\Models\SmsMessage([
                            'title' => 'Birthday Wish',
                            'content' => 'Happy Birthday {$recipient->name}! May God bless you abundantly on your special day.',
                            'recipient_group' => 'custom',
                            'recipient_ids' => [$recipient->id],
                            'status' => 'queued',
                        ]);
                        $smsMessage->send();
                    }
                }

                // Send Push Notification if recipient has an expo_push_token
                if ($recipient && !empty($recipient->expo_push_token)) {
                    $expoService = new \App\Services\ExpoNotificationService;
                    if ($recipient instanceof \App\Models\Member) {
                        $expoService->notifyMember($recipient, $notification->title, $notification->message, $notification->data ?? []);
                    } elseif ($recipient instanceof \App\Models\User) {
                        $expoService->notifyUser($recipient, $notification->title, $notification->message, $notification->data ?? []);
                    }
                }

                $notification->markAsSent();
            } catch (\Exception $e) {
                $notification->markAsFailed();
                // Log the error
                \Illuminate\Support\Facades\Log::error('Failed to process notification: '.$e->getMessage(), [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check for member absences and create notifications.
     * This method should be run periodically (e.g., weekly) to track attendance patterns.
     */
    public function checkAndNotifyAbsences()
    {
        $members = Member::all();
        $thresholdWeeks = 1; // Number of weeks of absence before notification

        foreach ($members as $member) {
            // Get the member's last attendance
            $lastAttendance = $member->attendances()
                ->orderBy('check_in_time', 'desc')
                ->first();

            if (! $lastAttendance || $lastAttendance->check_in_time->diffInWeeks(now()) >= $thresholdWeeks) {
                // Check if we already have a pending absence notification for this member
                $existingNotification = Notification::where('recipient_id', $member->id)
                    ->where('recipient_type', Member::class)
                    ->where('type', Notification::TYPE_ABSENCE)
                    ->whereIn('status', [Notification::STATUS_PENDING, Notification::STATUS_SCHEDULED])
                    ->first();

                if (! $existingNotification) {
                    $weeksAbsent = $lastAttendance ? $lastAttendance->check_in_time->diffInWeeks(now()) : $thresholdWeeks;
                    $message = $lastAttendance
                        ? "Member has not attended any service for {$weeksAbsent} weeks. Last attendance was on {$lastAttendance->check_in_time->format('M d, Y')}"
                        : "Member has not attended any service in the past {$thresholdWeeks} weeks";

                    Notification::create([
                        'type' => Notification::TYPE_ABSENCE,
                        'title' => 'Member Absence Alert',
                        'message' => "Absence notification for {$member->name}: {$message}",
                        'recipient_id' => $member->id,
                        'recipient_type' => Member::class,
                        'data' => [
                            'weeks_absent' => $weeksAbsent,
                            'last_attendance_date' => $lastAttendance ? $lastAttendance->check_in_time : null,
                            'member_phone' => $member->phone,
                            'member_email' => $member->email,
                        ],
                        'status' => Notification::STATUS_PENDING,
                    ]);
                }
            }
        }
    }

    /**
     * Schedule admin birthday notifications for all members' birthdays.
     */
    public function scheduleAdminBirthdayNotifications()
    {
        $members = Member::whereNotNull('date_of_birth')->get();
        $adminRoles = ['super_admin', 'admin', 'curate_pastor', 'attendance_manager', 'pa'];
        $admins = \App\Models\User::whereIn('role', $adminRoles)->get();

        if ($admins->isEmpty()) {
            return;
        }

        foreach ($members as $member) {
            $nextBirthday = Carbon::parse($member->date_of_birth)
                ->setYear(now()->year);

            if ($nextBirthday->isPast()) {
                $nextBirthday->addYear();
            }

            // 1. Schedule 1 week before (7 days before)
            $oneWeekBefore = $nextBirthday->copy()->subDays(7)->setTime(8, 0);
            
            // 2. Schedule on the day
            $onTheDay = $nextBirthday->copy()->setTime(8, 0);

            foreach ($admins as $admin) {
                // Schedule 1 week before
                if ($oneWeekBefore->isFuture()) {
                    $exists = Notification::where('recipient_id', $admin->id)
                        ->where('recipient_type', \App\Models\User::class)
                        ->where('type', Notification::TYPE_ADMIN_BIRTHDAY_REMINDER)
                        ->where('data->member_id', $member->id)
                        ->where('data->reminder_type', 'week_before')
                        ->where('data->birthday_date', $nextBirthday->format('Y-m-d'))
                        ->exists();

                    if (!$exists) {
                        Notification::create([
                            'type' => Notification::TYPE_ADMIN_BIRTHDAY_REMINDER,
                            'title' => 'Upcoming Member Birthday',
                            'message' => "Upcoming birthday for {$member->name} in a week on {$nextBirthday->format('M j')}.",
                            'recipient_id' => $admin->id,
                            'recipient_type' => \App\Models\User::class,
                            'data' => [
                                'member_id' => $member->id,
                                'birthday_date' => $nextBirthday->format('Y-m-d'),
                                'reminder_type' => 'week_before',
                                'age' => $nextBirthday->diffInYears($member->date_of_birth),
                            ],
                            'status' => Notification::STATUS_SCHEDULED,
                            'scheduled_at' => $oneWeekBefore,
                        ]);
                    }
                }

                // Schedule on the day
                if ($onTheDay->isFuture()) {
                    $exists = Notification::where('recipient_id', $admin->id)
                        ->where('recipient_type', \App\Models\User::class)
                        ->where('type', Notification::TYPE_ADMIN_BIRTHDAY_REMINDER)
                        ->where('data->member_id', $member->id)
                        ->where('data->reminder_type', 'on_day')
                        ->where('data->birthday_date', $nextBirthday->format('Y-m-d'))
                        ->exists();

                    if (!$exists) {
                        Notification::create([
                            'type' => Notification::TYPE_ADMIN_BIRTHDAY_REMINDER,
                            'title' => 'Member Birthday Today',
                            'message' => "{$member->name} is celebrating their birthday today! Wish them a happy birthday.",
                            'recipient_id' => $admin->id,
                            'recipient_type' => \App\Models\User::class,
                            'data' => [
                                'member_id' => $member->id,
                                'birthday_date' => $nextBirthday->format('Y-m-d'),
                                'reminder_type' => 'on_day',
                                'age' => $nextBirthday->diffInYears($member->date_of_birth),
                            ],
                            'status' => Notification::STATUS_SCHEDULED,
                            'scheduled_at' => $onTheDay,
                        ]);
                    }
                }
            }
        }
    }
}
