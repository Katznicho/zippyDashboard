<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agent extends Authenticatable
{
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
        'referal_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function account(): HasOne
    {
        return $this->hasOne(UserAccount::class);
    }

    //user has a device
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
        return $this->hasMany(Property::class, );
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
}