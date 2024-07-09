<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',
        'type',
        'amount', 
        'phone_number',
        'payment_mode',
        'payment_method',
        'description',
        'reference',
        'status',
        'property_id',
       'app_user_id',
    ];


    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class);
    }
}
