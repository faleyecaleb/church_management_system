<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'prayer_request_id',
        'member_id',
        'notes',
        'prayed_at'
    ];

    protected $casts = [
        'prayed_at' => 'datetime'
    ];

    // Relationships
    public function prayerRequest()
    {
        return $this->belongsTo(PrayerRequest::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Scopes
    public function scopeByMember($query, $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    public function scopeByRequest($query, $requestId)
    {
        return $query->where('prayer_request_id', $requestId);
    }

    public function scopeWithNotes($query)
    {
        return $query->whereNotNull('notes');
    }

    // Helper methods
    public function getPrayerBy()
    {
        if (!$this->member_id) {
            return 'Anonymous';
        }

        return $this->member->full_name;
    }

    public static function getPrayerStats($memberId = null, $startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($memberId) {
            $query->where('member_id', $memberId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('prayed_at', [$startDate, $endDate]);
        }

        $prayers = $query->get();

        return [
            'total_prayers' => $prayers->count(),
            'prayers_with_notes' => $prayers->whereNotNull('notes')->count(),
            'unique_requests_prayed' => $prayers->unique('prayer_request_id')->count(),
            'by_request' => $prayers->groupBy('prayer_request_id')
                ->map(fn ($items) => $items->count()),
            'by_member' => $prayers->groupBy('member_id')
                ->map(fn ($items) => $items->count())
        ];
    }

    public static function getTopPrayerWarriors($limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::query()
            ->selectRaw('member_id, COUNT(*) as prayer_count')
            ->whereNotNull('member_id')
            ->groupBy('member_id');

        if ($startDate && $endDate) {
            $query->whereBetween('prayed_at', [$startDate, $endDate]);
        }

        return $query->orderByDesc('prayer_count')
            ->limit($limit)
            ->with('member')
            ->get()
            ->map(function ($prayer) {
                return [
                    'member' => $prayer->member->full_name,
                    'count' => $prayer->prayer_count
                ];
            });
    }

    public static function getMostPrayedRequests($limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::query()
            ->selectRaw('prayer_request_id, COUNT(*) as prayer_count')
            ->groupBy('prayer_request_id');

        if ($startDate && $endDate) {
            $query->whereBetween('prayed_at', [$startDate, $endDate]);
        }

        return $query->orderByDesc('prayer_count')
            ->limit($limit)
            ->with('prayerRequest')
            ->get()
            ->map(function ($prayer) {
                return [
                    'request' => $prayer->prayerRequest->title,
                    'count' => $prayer->prayer_count
                ];
            });
    }

    public function notifyRequestCreator()
    {
        if ($this->prayerRequest->member_id && !$this->prayerRequest->is_anonymous) {
            // TODO: Implement notification logic
            // Notify the prayer request creator that someone has prayed for their request
        }
    }
}