<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class PropertyOwner extends Authenticatable
{
    protected $table = 'property_owners';
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role',
        'is_admin',
        'is_user_verified',
        'otp',
        'otp_send_time',
        'device_token',
        'email_verified_at',
        'avatar',
        'is_new_user',
        'referrer_id',
        'lat',
        'long',
        'property_owner_verified',
        'avatar',
        'points',
        'current_points',
        'login_type',
        'agent_id'
    ];

    //relationships

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }


    public function bookings()
    {
        return $this->hasMany(Booking::class, 'owner_id');
    }


    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    

    
}