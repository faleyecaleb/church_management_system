<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EquipmentBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'member_id',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    // Relationships
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', Carbon::now())
                     ->where('status', 'approved')
                     ->orderBy('start_time');
    }

    public function scopeActive($query)
    {
        return $query->where('start_time', '<=', Carbon::now())
                     ->where('end_time', '>=', Carbon::now())
                     ->where('status', 'approved');
    }

    // Helper methods
    public function approve()
    {
        if ($this->equipment->isAvailable($this->start_time, $this->end_time)) {
            $this->update(['status' => 'approved']);
            
            // Update equipment status if booking starts now
            if ($this->start_time <= Carbon::now()) {
                $this->equipment->markAsInUse();
            }

            // TODO: Send notification to member
            return true;
        }
        return false;
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason
        ]);

        // TODO: Send notification to member
    }

    public function complete()
    {
        $this->update(['status' => 'completed']);
        $this->equipment->markAsAvailable();

        // TODO: Send feedback request to member
    }

    public function cancel()
    {
        if ($this->start_time > Carbon::now()) {
            $this->update(['status' => 'cancelled']);
            return true;
        }
        return false;
    }

    public function getDurationInHours()
    {
        return $this->start_time->diffInHours($this->end_time);
    }

    public function isOverdue()
    {
        return $this->status === 'approved' && 
               $this->end_time < Carbon::now();
    }

    public static function getBookingStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate]);
        }

        $bookings = $query->get();

        return [
            'total_bookings' => $bookings->count(),
            'pending_bookings' => $bookings->where('status', 'pending')->count(),
            'approved_bookings' => $bookings->where('status', 'approved')->count(),
            'rejected_bookings' => $bookings->where('status', 'rejected')->count(),
            'completed_bookings' => $bookings->where('status', 'completed')->count(),
            'by_equipment' => $bookings->groupBy('equipment_id')
                ->map(fn ($items) => [
                    'total' => $items->count(),
                    'hours' => $items->sum(function ($booking) {
                        return $booking->getDurationInHours();
                    })
                ]),
            'by_member' => $bookings->groupBy('member_id')
                ->map(fn ($items) => $items->count())
        ];
    }

    public static function checkAndUpdateStatuses()
    {
        // Complete bookings that have ended
        self::approved()
            ->where('end_time', '<', Carbon::now())
            ->get()
            ->each(function ($booking) {
                $booking->complete();
            });

        // Start bookings that should begin now
        self::approved()
            ->where('start_time', '<=', Carbon::now())
            ->where('end_time', '>', Carbon::now())
            ->get()
            ->each(function ($booking) {
                $booking->equipment->markAsInUse();
            });
    }

    public static function getConflictingBookings($equipmentId, $startTime, $endTime)
    {
        return self::where('equipment_id', $equipmentId)
            ->where('status', 'approved')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->get();
    }
}