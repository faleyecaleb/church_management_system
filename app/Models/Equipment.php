<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'serial_number',
        'description',
        'location',
        'status',
        'purchase_date',
        'purchase_price',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_history'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'maintenance_history' => 'array'
    ];

    // Relationships
    public function bookings()
    {
        return $this->hasMany(EquipmentBooking::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeInUse($query)
    {
        return $query->where('status', 'in_use');
    }

    public function scopeInMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('next_maintenance_date', '<=', Carbon::today());
    }

    // Helper methods
    public function isAvailable($startTime, $endTime)
    {
        if ($this->status !== 'available') {
            return false;
        }

        return !$this->bookings()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->where('status', 'approved')
            ->exists();
    }

    public function recordMaintenance($description, $cost = null, $performedBy = null)
    {
        $maintenance = [
            'date' => Carbon::now()->toDateString(),
            'description' => $description,
            'cost' => $cost,
            'performed_by' => $performedBy
        ];

        $history = $this->maintenance_history ?? [];
        array_push($history, $maintenance);

        $this->update([
            'last_maintenance_date' => Carbon::now(),
            'next_maintenance_date' => Carbon::now()->addMonths(3), // Default 3 months
            'maintenance_history' => $history,
            'status' => 'available'
        ]);
    }

    public function scheduleNextMaintenance($date)
    {
        $this->update(['next_maintenance_date' => $date]);
    }

    public static function getInventoryStats()
    {
        $equipment = self::all();

        return [
            'total_equipment' => $equipment->count(),
            'available' => $equipment->where('status', 'available')->count(),
            'in_use' => $equipment->where('status', 'in_use')->count(),
            'in_maintenance' => $equipment->where('status', 'maintenance')->count(),
            'needs_maintenance' => $equipment->filter(function ($item) {
                return $item->next_maintenance_date <= Carbon::today();
            })->count(),
            'total_value' => $equipment->sum('purchase_price'),
            'by_category' => $equipment->groupBy('category')
                ->map(fn ($items) => [
                    'count' => $items->count(),
                    'value' => $items->sum('purchase_price')
                ]),
            'by_location' => $equipment->groupBy('location')
                ->map(fn ($items) => $items->count())
        ];
    }

    public function getMaintenanceCosts($startDate = null, $endDate = null)
    {
        $history = collect($this->maintenance_history ?? []);

        if ($startDate) {
            $history = $history->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $history = $history->where('date', '<=', $endDate);
        }

        return $history->sum('cost');
    }

    public function getUpcomingBookings($limit = 5)
    {
        return $this->bookings()
            ->where('start_time', '>', Carbon::now())
            ->where('status', 'approved')
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    public function markAsInUse()
    {
        $this->update(['status' => 'in_use']);
    }

    public function markAsAvailable()
    {
        $this->update(['status' => 'available']);
    }

    public function markAsInMaintenance()
    {
        $this->update(['status' => 'maintenance']);
    }
}