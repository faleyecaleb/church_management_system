<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'document_type', // baptism_certificate, id_card, etc.
        'issue_date',
        'expiry_date',
        'is_verified',
        'verified_by',
        'verification_date',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verification_date' => 'date',
        'is_verified' => 'boolean',
    ];

    // Relationship with Member
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Relationship with User who verified the document
    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scope for verified documents
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Scope for unverified documents
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    // Scope for specific document types
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    // Check if document is expired
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Get remaining days until expiry
    public function daysUntilExpiry()
    {
        return $this->expiry_date ? now()->diffInDays($this->expiry_date, false) : null;
    }

    // Get document size in human readable format
    public function getHumanReadableSize()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }
}