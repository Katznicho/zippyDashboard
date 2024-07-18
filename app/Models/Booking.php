<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'check_in_date',
        'check_out_date',
        'is_approved',
        'total_price',
        'duration_in_days',
        'payment_id',
        'app_user_id',
        'owner_id',
        'agent_id',
    ];


    public function property()
    {
        return $this->belongsTo(Property::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class);
    }


    public function owner()
    {
        return $this->belongsTo(PropertyOwner::class, 'owner_id');
    }


    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }


    //booking has a payment
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
