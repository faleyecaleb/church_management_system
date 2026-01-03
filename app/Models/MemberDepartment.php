<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberDepartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'department_id',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public static function getDepartmentOptions()
    {
        return Department::where('is_active', true)->pluck('name', 'id')->toArray();
    }
}