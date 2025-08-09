<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'complainant_name',
        'complainant_email',
        'complainant_phone',
        'department',
        'category',
        'priority',
        'status',
        'subject',
        'description',
        'evidence_files',
        'assigned_to',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
        'escalated_at',
        'escalated_to',
        'is_anonymous',
        'follow_up_required',
        'follow_up_date',
        'satisfaction_rating',
        'satisfaction_feedback',
    ];

    protected $casts = [
        'evidence_files' => 'array',
        'resolved_at' => 'datetime',
        'escalated_at' => 'datetime',
        'follow_up_date' => 'date',
        'is_anonymous' => 'boolean',
        'follow_up_required' => 'boolean',
        'satisfaction_rating' => 'integer',
    ];

    protected $appends = [
        'formatted_priority',
        'time_to_resolution',
        'is_overdue',
        'complainant_display_name',
    ];

    // Constants for enums
    const CATEGORIES = [
        'service_quality' => 'Service Quality',
        'facility' => 'Facility Issues',
        'staff_behavior' => 'Staff Behavior',
        'financial' => 'Financial Matters',
        'pastoral_care' => 'Pastoral Care',
        'communication' => 'Communication',
        'event_management' => 'Event Management',
        'other' => 'Other',
    ];

    const PRIORITIES = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
    ];

    const STATUSES = [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'pending_review' => 'Pending Review',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
        'escalated' => 'Escalated',
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function responses()
    {
        return $this->hasMany(ComplaintResponse::class)->orderBy('created_at');
    }

    public function publicResponses()
    {
        return $this->hasMany(ComplaintResponse::class)
            ->where('is_internal', false)
            ->orderBy('created_at');
    }

    public function internalResponses()
    {
        return $this->hasMany(ComplaintResponse::class)
            ->where('is_internal', true)
            ->orderBy('created_at');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'pending_review']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('follow_up_required', true)
            ->where('follow_up_date', '<', now())
            ->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeRequiringFollowUp($query)
    {
        return $query->where('follow_up_required', true)
            ->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('subject', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('complainant_name', 'like', "%{$search}%")
              ->orWhereHas('member', function ($memberQuery) use ($search) {
                  $memberQuery->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
              });
        });
    }

    // Accessors
    public function getFormattedPriorityAttribute()
    {
        return self::PRIORITIES[$this->priority] ?? $this->priority;
    }

    public function getTimeToResolutionAttribute()
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->resolved_at, true);
    }

    public function getIsOverdueAttribute()
    {
        if (!$this->follow_up_required || in_array($this->status, ['resolved', 'closed'])) {
            return false;
        }

        return $this->follow_up_date && $this->follow_up_date->isPast();
    }

    public function getComplainantDisplayNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        if ($this->member) {
            return $this->member->full_name;
        }

        return $this->complainant_name ?? 'Unknown';
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'pending_review' => 'purple',
            'resolved' => 'green',
            'closed' => 'gray',
            'escalated' => 'red',
            default => 'gray',
        };
    }

    // Methods
    public function assign($userId, $assignedBy = null)
    {
        $this->update(['assigned_to' => $userId]);

        $this->addResponse([
            'user_id' => $assignedBy ?? auth()->id(),
            'response_type' => 'assignment',
            'message' => "Complaint assigned to " . $this->assignedTo->name,
            'is_internal' => true,
            'metadata' => ['assigned_to' => $userId],
        ]);

        return $this;
    }

    public function escalate($escalatedTo, $reason = null, $escalatedBy = null)
    {
        $this->update([
            'status' => 'escalated',
            'escalated_to' => $escalatedTo,
            'escalated_at' => now(),
        ]);

        $message = "Complaint escalated to " . $this->escalatedTo->name;
        if ($reason) {
            $message .= ". Reason: " . $reason;
        }

        $this->addResponse([
            'user_id' => $escalatedBy ?? auth()->id(),
            'response_type' => 'escalation',
            'message' => $message,
            'is_internal' => true,
            'metadata' => [
                'escalated_to' => $escalatedTo,
                'reason' => $reason,
            ],
        ]);

        return $this;
    }

    public function resolve($resolutionNotes, $resolvedBy = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolution_notes' => $resolutionNotes,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy ?? auth()->id(),
        ]);

        $this->addResponse([
            'user_id' => $resolvedBy ?? auth()->id(),
            'response_type' => 'resolution',
            'message' => $resolutionNotes,
            'is_internal' => false,
        ]);

        return $this;
    }

    public function updateStatus($newStatus, $reason = null, $updatedBy = null)
    {
        $oldStatus = $this->status;
        $this->update(['status' => $newStatus]);

        $message = "Status changed from " . self::STATUSES[$oldStatus] . " to " . self::STATUSES[$newStatus];
        if ($reason) {
            $message .= ". " . $reason;
        }

        $this->addResponse([
            'user_id' => $updatedBy ?? auth()->id(),
            'response_type' => 'status_update',
            'message' => $message,
            'is_internal' => true,
            'metadata' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
            ],
        ]);

        return $this;
    }

    public function addResponse(array $responseData)
    {
        return $this->responses()->create($responseData);
    }

    public function setFollowUp($date, $required = true)
    {
        $this->update([
            'follow_up_required' => $required,
            'follow_up_date' => $date,
        ]);

        return $this;
    }

    public function addSatisfactionRating($rating, $feedback = null)
    {
        $this->update([
            'satisfaction_rating' => $rating,
            'satisfaction_feedback' => $feedback,
        ]);

        return $this;
    }

    // Static methods
    public static function getStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total' => $query->count(),
            'open' => $query->clone()->open()->count(),
            'resolved' => $query->clone()->where('status', 'resolved')->count(),
            'urgent' => $query->clone()->where('priority', 'urgent')->count(),
            'overdue' => $query->clone()->overdue()->count(),
            'by_category' => $query->clone()->groupBy('category')
                ->selectRaw('category, count(*) as count')
                ->pluck('count', 'category'),
            'by_department' => $query->clone()->whereNotNull('department')
                ->groupBy('department')
                ->selectRaw('department, count(*) as count')
                ->pluck('count', 'department'),
            'avg_resolution_time' => $query->clone()->whereNotNull('resolved_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                ->value('avg_hours'),
        ];
    }

    public static function getDepartmentOptions()
    {
        return MemberDepartment::getDepartmentOptions();
    }
}