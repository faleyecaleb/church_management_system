<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'user_id',
        'response_type',
        'message',
        'is_internal',
        'metadata',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'metadata' => 'array',
    ];

    // Constants
    const RESPONSE_TYPES = [
        'comment' => 'Comment',
        'status_update' => 'Status Update',
        'resolution' => 'Resolution',
        'escalation' => 'Escalation',
        'assignment' => 'Assignment',
        'follow_up' => 'Follow-up',
    ];

    // Relationships
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('response_type', $type);
    }

    // Accessors
    public function getFormattedTypeAttribute()
    {
        return self::RESPONSE_TYPES[$this->response_type] ?? $this->response_type;
    }

    public function getTypeColorAttribute()
    {
        return match($this->response_type) {
            'comment' => 'blue',
            'status_update' => 'yellow',
            'resolution' => 'green',
            'escalation' => 'red',
            'assignment' => 'purple',
            'follow_up' => 'orange',
            default => 'gray',
        };
    }
}