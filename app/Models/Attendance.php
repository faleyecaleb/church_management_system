<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'service_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'check_in_method',
        'check_in_location',
        'checked_in_by',
        'checked_out_by',
        'qr_code',
        'notes',
        'is_present',
        'is_absent',
        'status'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'is_present' => 'boolean',
        'is_absent' => 'boolean',
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
    
    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('check_in_time', Carbon::today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('check_in_time', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('check_in_time', Carbon::now()->month)
                     ->whereYear('check_in_time', Carbon::now()->year);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    public function scopeByCheckInMethod($query, $method)
    {
        return $query->where('check_in_method', $method);
    }

    // Helper methods
    public static function generateQRCode()
    {
        return uniqid('ATT_', true);
    }

    public static function getAttendanceStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate) {
            $query->where('check_in_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('check_in_time', '<=', $endDate);
        }

        return [
            'total' => $query->count(),
            'by_service' => $query->groupBy('service_type')
                                 ->selectRaw('service_type, count(*) as count')
                                 ->pluck('count', 'service_type'),
            'by_method' => $query->groupBy('check_in_method')
                                ->selectRaw('check_in_method, count(*) as count')
                                ->pluck('count', 'check_in_method')
        ];
    }

    public static function markAttendance($memberId, $serviceType, $checkInMethod = 'qr_code')
    {
        return self::create([
            'member_id' => $memberId,
            'service_type' => $serviceType,
            'check_in_time' => Carbon::now(),
            'check_in_method' => $checkInMethod,
            'qr_code' => $checkInMethod === 'qr_code' ? self::generateQRCode() : null
        ]);
    }
}