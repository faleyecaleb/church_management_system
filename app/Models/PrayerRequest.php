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
        'requestor_id',
        'title',
        'description',
        'is_anonymous',
        'is_private',
        'is_public',
        'status',
        'prayer_count',
        'prayer_target',
        'prayer_frequency',
        'end_date',
        'last_prayed_at'
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_private' => 'boolean',
        'is_public' => 'boolean',
        'prayer_count' => 'integer',
        'prayer_target' => 'integer',
        'prayer_frequency' => 'integer',
        'end_date' => 'date',
        'last_prayed_at' => 'datetime'
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
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
    public function recordPrayer($userId = null, $notes = null, $memberId = null)
    {
        $this->increment('prayer_count');
        $this->update(['last_prayed_at' => Carbon::now()]);

        return $this->prayers()->create([
            'user_id' => $userId,
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

    public static function getOverallStats($startDate = null, $endDate = null)
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

    public function canBeViewedBy($user)
    {
        // Public requests are visible to everyone
        if (!$this->is_private && $this->is_public) {
            return true;
        }

        // If no user is provided, only public requests can be viewed
        if (!$user) {
            return false;
        }

        // Private requests can only be viewed by the creator, member, or admin
        return $user->id === $this->requestor_id || 
               $user->id === $this->member_id || 
               $user->role === 'admin' ||
               ($user instanceof \App\Models\Member && $user->hasRole('admin'));
    }

    public function canBeEditedBy($user)
    {
        if (!$user) {
            return false;
        }

        return $user->id === $this->requestor_id || 
               $user->id === $this->member_id || 
               $user->role === 'admin' ||
               ($user instanceof \App\Models\Member && $user->hasRole('admin'));
    }

    public function getPrayerStats()
    {
        return [
            'total_prayers' => $this->prayer_count ?? 0,
            'days_in_prayer' => $this->created_at->diffInDays(now()),
            'last_prayed' => $this->last_prayed_at,
            'needs_prayer' => !$this->last_prayed_at || $this->last_prayed_at->diffInDays(now()) >= 7,
            'prayer_target' => $this->prayer_target,
            'target_progress' => $this->prayer_target ? round((($this->prayer_count ?? 0) / $this->prayer_target) * 100) : null,
        ];
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