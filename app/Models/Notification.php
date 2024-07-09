<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        
        'user_id',
        'title',
        'body',
        'is_read',
        'property_id',
        'type',
        'message',
        'app_user_id',
        'type'
        
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appUser(){

        return $this->belongsTo(AppUser::class);
    }

    
}
