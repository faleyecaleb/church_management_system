<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'day_of_week', // 0 (Sunday) through 6 (Saturday)
        'start_time',
        'end_time',
        'location',
        'is_recurring',
        'capacity',
        'status', // active, cancelled, etc.
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    protected $appends = ['day_of_week_name'];

    public function getDayOfWeekNameAttribute()
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return $days[$this->day_of_week] ?? null;
    }

    /**
     * Get the attendances for this service.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the order of service items for this service.
     */
    public function orderOfServices()
    {
        return $this->hasMany(OrderOfService::class);
    }

    /**
     * Get the attendance count for this service.
     */
    public function getAttendanceCountAttribute()
    {
        return $this->attendances()->count();
    }

    /**
     * Check if the service is at capacity.
     */
    public function isAtCapacity()
    {
        return $this->capacity && $this->attendance_count >= $this->capacity;
    }

    /**
     * Get the remaining capacity for this service.
     */
    public function getRemainingCapacityAttribute()
    {
        if (!$this->capacity) {
            return null;
        }
        return max(0, $this->capacity - $this->attendance_count);
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include services for a specific day of the week.
     */
    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope a query to only include recurring services.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Get the next occurrence of this service.
     */
    public function getNextOccurrenceAttribute()
    {
        if (!$this->is_recurring) {
            return null;
        }

        $now = now();
        $nextDate = $now->copy();

        // If today is the service day but it's past the start time,
        // or if today is past the service day, move to next week
        if (($now->dayOfWeek === $this->day_of_week && $now->greaterThan($this->start_time)) ||
            $now->dayOfWeek > $this->day_of_week) {
            $nextDate = $nextDate->next($this->day_of_week);
        } elseif ($now->dayOfWeek < $this->day_of_week) {
            $nextDate = $nextDate->next($this->day_of_week);
        }

        return $nextDate->setTimeFromTimeString($this->start_time->format('H:i:s'));
    }

    /**
     * Check if check-in is currently allowed for this service.
     */
    public function isCheckInAllowed()
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->isAtCapacity()) {
            return false;
        }

        $now = now();
        $serviceTime = $this->start_time;
        $gracePeriod = config('church.attendance.grace_period', 15); // 15 minutes default

        return $now->between(
            $serviceTime->copy()->subHours(1), // Allow check-in 1 hour before service
            $serviceTime->copy()->addMinutes($gracePeriod) // Until grace period ends
        );
    }
}