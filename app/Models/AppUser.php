<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AppUser extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $appends = ['total_points', 'used_points', 'current_points'];


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
        'picture',
        'points',
        'current_points',
        'provider'
    ];

    public function device(): HasOne
    {
        return $this->hasOne(UserDevice::class);
    }

    //user has many notifications
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    //user has many properties
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    //user has many bookings
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

  // a user uses points
    public function pointUsages()
    {
        return $this->hasMany(PointUsage::class);
    }

    public function userPoints()
    {
        return $this->hasMany(UserPoint::class);
    }

    // Calculate used points
    public function getUsedPointsAttribute()
    {
        return $this->pointUsages()->sum('points') ?? 0;
    }

    // Calculate total points
    public function getTotalPointsAttribute()
    {
        return $this->userPoints()->sum('points') ??0;
    }

    // Calculate current points
    public function getCurrentPointsAttribute()
    {
        return $this->points - $this->getUsedPointsAttribute() ?? 0;
    }
}