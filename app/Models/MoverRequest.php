<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoverRequest extends Model
{
    use HasFactory;


    protected $fillable = [
        'car_type',
        'moved_item',
        'pickup_address',
        'pickup_lat',
        'pickup_long',
        'dropoff_address',
        'dropoff_lat',
        'dropoff_long',
        'price',
        'payment_method',
        'status',
        'pickup_date',
        'dropoff_date',
        'notes',
        'app_user_id',
        'user_id',
        'admin_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appUser()
    {
        return $this->belongsTo(AppUser::class);
    }
}
