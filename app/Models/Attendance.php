<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'service_type',
        'check_in_time',
        'check_in_method',
        'qr_code',
        'notes'
    ];

    protected $casts = [
        'check_in_time' => 'datetime'
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
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

    public function scopeByService($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
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