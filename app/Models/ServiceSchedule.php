<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ServiceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'day_of_week',
        'start_time',
        'end_time',
        'description',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    // Helper methods
    public static function getUpcomingServices($limit = 5)
    {
        $today = Carbon::now();
        $dayOfWeek = $today->format('l');
        $currentTime = $today->format('H:i:s');

        return self::active()
            ->where(function ($query) use ($dayOfWeek, $currentTime) {
                $query->where('day_of_week', $dayOfWeek)
                      ->where('start_time', '>', $currentTime)
                      ->orWhere('day_of_week', '!=', $dayOfWeek);
            })
            ->orderByRaw("CASE 
                WHEN day_of_week = ? THEN 0
                ELSE FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
                END", [$dayOfWeek])
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    public function isCurrentlyActive()
    {
        $now = Carbon::now();
        $currentDayOfWeek = $now->format('l');
        $currentTime = $now->format('H:i:s');

        return $this->is_active &&
               $this->day_of_week === $currentDayOfWeek &&
               $currentTime >= $this->start_time->format('H:i:s') &&
               $currentTime <= $this->end_time->format('H:i:s');
    }

    public function getNextOccurrence()
    {
        $now = Carbon::now();
        $targetDay = Carbon::parse("next {$this->day_of_week}");

        if ($this->day_of_week === $now->format('l') &&
            $now->format('H:i:s') < $this->start_time->format('H:i:s')) {
            $targetDay = $now;
        }

        return $targetDay->setTimeFromTimeString($this->start_time->format('H:i:s'));
    }

    public function getDurationInMinutes()
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    public static function getServicesByDay()
    {
        return self::active()
            ->orderByRaw("FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');
    }
}