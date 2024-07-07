<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        
        'points',
        'description',
        'payment_id',
        'description',
        'reference',
        'app_user_id',
        'status'
    ];

    public function appUser()
    {
        return $this->belongsTo(AppUser::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
