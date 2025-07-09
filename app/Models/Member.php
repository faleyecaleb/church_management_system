<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'baptism_date',
        'membership_status',
        'profile_photo',
        'custom_fields',
        'gender',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'baptism_date' => 'date',
        'custom_fields' => 'array',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return Storage::disk('public')->url($this->profile_photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name);
    }

    public function pledges()
    {
        return $this->hasMany(Pledge::class);
    }

    public function prayerRequests()
    {
        return $this->hasMany(PrayerRequest::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(InternalMessage::class, 'sender_id');
    }

    public function equipmentBookings()
    {
        return $this->hasMany(EquipmentBooking::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'member_role');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function documents()
    {
        return $this->hasMany(MemberDocument::class);
    }

    public function departments()
    {
        return $this->hasMany(MemberDepartment::class);
    }

    // Helper method to get department names as array
    public function getDepartmentNamesAttribute()
    {
        return $this->departments->pluck('department')->toArray();
    }

    // Helper method to get departments as comma-separated string
    public function getDepartmentListAttribute()
    {
        return $this->departments->pluck('department')->join(', ');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Helper methods
    public function hasRole($roleName)
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission($permissionName)
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })->exists();
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function getAttendanceRate($startDate = null, $endDate = null)
    {
        $query = $this->attendances();
        
        if ($startDate) {
            $query->where('check_in_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('check_in_time', '<=', $endDate);
        }

        return $query->count();
    }

    public function getTotalDonations($year = null)
    {
        $query = $this->donations();

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        return $query->sum('amount');
    }
}