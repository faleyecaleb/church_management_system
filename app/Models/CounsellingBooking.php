<?php

namespace App\Models;

use App\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CounsellingBooking extends Model
{
    use HasFactory, SoftDeletes, BelongsToChurch;

    protected $fillable = [
        'member_id',
        'church_id',
        'requested_date',
        'requested_time',
        'reason',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'requested_time' => 'datetime:H:i',
    ];

    /**
     * Get the member that requested the booking.
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
