<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_id',
        'name',
        'relationship',
        'phone',
        'alternate_phone',
        'email',
        'address',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Relationship with Member
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Scope for primary contacts
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Scope for non-primary contacts
    public function scopeSecondary($query)
    {
        return $query->where('is_primary', false);
    }
}