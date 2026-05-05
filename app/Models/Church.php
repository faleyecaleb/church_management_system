<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description'
    ];

    /**
     * Get the users associated with this church.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Get the members associated with this church.
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
