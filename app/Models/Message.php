<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',          // 'sms', 'prayer', 'internal'
        'sender_id',     // ID of the user sending the message
        'recipient_type', // 'individual', 'group'
        'recipient_id',   // ID of recipient (user_id or group_id)
        'subject',
        'content',
        'template_id',    // Optional template reference
        'status',        // 'pending', 'sent', 'delivered', 'failed'
        'scheduled_at',   // For scheduled messages
        'sent_at',       // When the message was sent
        'delivered_at',  // When delivery was confirmed
        'read_at',       // For internal messages
        'metadata'       // JSON field for additional data
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Relationships
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->morphTo();
    }

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    // Scopes
    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }

    public function scopePrayer($query)
    {
        return $query->where('type', 'prayer');
    }

    public function scopeInternal($query)
    {
        return $query->where('type', 'internal');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                     ->where('scheduled_at', '>', now());
    }

    // Methods
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }

    public function markAsRead()
    {
        if ($this->type === 'internal') {
            $this->update(['read_at' => now()]);
        }
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'metadata' => array_merge($this->metadata ?? [], ['failure_reason' => $reason])
        ]);
    }
}