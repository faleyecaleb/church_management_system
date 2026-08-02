<?php

namespace App\Models;

use App\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileUpdateRequest extends Model
{
    use HasFactory, BelongsToChurch;

    protected $fillable = [
        'member_id',
        'church_id',
        'requested_data',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'requested_data' => 'array',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
