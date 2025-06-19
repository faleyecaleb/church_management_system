<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'status',
        'start_date',
        'end_date',
        'notes',
        'changed_by',
        'class_completed',
        'transfer_church',
        'transfer_date',
        'renewal_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'transfer_date' => 'datetime',
        'renewal_date' => 'datetime',
        'class_completed' => 'boolean'
    ];

    // Status constants
    const STATUS_NEW = 'new';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_TRANSFERRED = 'transferred';
    const STATUS_PENDING = 'pending';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New Member',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_TRANSFERRED => 'Transferred',
            self::STATUS_PENDING => 'Pending'
        ];
    }

    /**
     * Get the member that owns this status
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the user who changed the status
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope for active records
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_date');
    }

    /**
     * Scope for specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the status is current
     */
    public function isCurrent(): bool
    {
        return is_null($this->end_date);
    }

    /**
     * Check if membership needs renewal
     */
    public function needsRenewal(): bool
    {
        if (!$this->renewal_date) {
            return false;
        }

        return $this->renewal_date->isPast();
    }

    /**
     * Check if member has completed membership class
     */
    public function hasCompletedClass(): bool
    {
        return $this->class_completed;
    }

    /**
     * Get the duration of this status in days
     */
    public function getDurationInDays(): int
    {
        $end = $this->end_date ?? now();
        return $this->start_date->diffInDays($end);
    }
}