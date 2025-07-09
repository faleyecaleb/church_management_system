<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'department',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public static function getDepartmentOptions()
    {
        return [
            'Media',
            'Choir', 
            'Ushers',
            'Dance',
            'Prayer',
            'Lost but Found',
            'Drama',
            'Sanctuary'
        ];
    }
}