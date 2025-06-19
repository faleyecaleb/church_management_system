<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PrayerRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'title',
        'description',
        'is_anonymous',
        'is_private',
        'status',
        'prayer_count',
        'last_prayed_at'
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_private' => 'boolean',
        'prayer_count' => 'integer',
        'last_prayed_at' => 'datetime'
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function prayers()
    {
        return $this->hasMany(Prayer::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    public function scopeRecentlyPrayed($query, $days = 7)
    {
        return $query->where('last_prayed_at', '>=', Carbon::now()->subDays($days));
    }

    public function scopeNeedsPrayer($query, $days = 7)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereNull('last_prayed_at')
              ->orWhere('last_prayed_at', '<', Carbon::now()->subDays($days));
        })->where('status', 'active');
    }

    // Helper methods
    public function recordPrayer($memberId = null, $notes = null)
    {
        $this->increment('prayer_count');
        $this->update(['last_prayed_at' => Carbon::now()]);

        return $this->prayers()->create([
            'member_id' => $memberId,
            'notes' => $notes,
            'prayed_at' => Carbon::now()
        ]);
    }

    public function markAsCompleted($completedBy = null, $notes = null)
    {
        $this->update([
            'status' => 'completed',
            'notes' => $notes ?? 'Marked as completed' . ($completedBy ? ' by ' . $completedBy : '')
        ]);

        // Notify the prayer request creator
        if ($this->member_id) {
            // TODO: Implement notification logic
        }
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function reactivate()
    {
        $this->update(['status' => 'active']);
    }

    public function getRequestorName()
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        return $this->member ? $this->member->full_name : 'Guest';
    }

    public static function getPrayerStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $requests = $query->get();

        return [
            'total_requests' => $requests->count(),
            'active_requests' => $requests->where('status', 'active')->count(),
            'completed_requests' => $requests->where('status', 'completed')->count(),
            'archived_requests' => $requests->where('status', 'archived')->count(),
            'total_prayers' => $requests->sum('prayer_count'),
            'anonymous_requests' => $requests->where('is_anonymous', true)->count(),
            'private_requests' => $requests->where('is_private', true)->count(),
            'needs_prayer' => $requests->where('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('last_prayed_at')
                          ->orWhere('last_prayed_at', '<', Carbon::now()->subDays(7));
                })->count(),
            'recently_prayed' => $requests->where('last_prayed_at', '>=', Carbon::now()->subDays(7))->count()
        ];
    }

    public function canBeViewedBy($member)
    {
        if (!$this->is_private) {
            return true;
        }

        if (!$member) {
            return false;
        }

        return $member->id === $this->member_id || $member->hasRole('admin');
    }

    public function canBeEditedBy($member)
    {
        if (!$member) {
            return false;
        }

        return $member->id === $this->member_id || $member->hasRole('admin');
    }

    public function getDaysInPrayer()
    {
        return $this->created_at->diffInDays(Carbon::now());
    }

    public function getAveragePrayersPerDay()
    {
        $days = max(1, $this->getDaysInPrayer());
        return round($this->prayer_count / $days, 2);
    }

    public function shouldSendReminder()
    {
        if ($this->status !== 'active') {
            return false;
        }

        return !$this->last_prayed_at || 
               $this->last_prayed_at->diffInDays(Carbon::now()) >= 7;
    }
    }