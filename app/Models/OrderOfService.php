<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderOfService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'program',
        'start_time',
        'end_time',
        'order',
        'description',
        'leader',
        'notes',
        'duration_minutes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the service that owns the order of service item.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the duration in minutes.
     */
    public function getDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        return $this->duration_minutes ?? 0;
    }

    /**
     * Scope to order by the order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Scope to get items for a specific service.
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Get the formatted time range.
     */
    public function getTimeRangeAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->format('h:i A') . ' - ' . $this->end_time->format('h:i A');
        }
        return 'Time not set';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orderOfService) {
            // Auto-set order if not provided
            if (!$orderOfService->order) {
                $maxOrder = static::where('service_id', $orderOfService->service_id)->max('order');
                $orderOfService->order = ($maxOrder ?? 0) + 1;
            }
        });
    }
}
