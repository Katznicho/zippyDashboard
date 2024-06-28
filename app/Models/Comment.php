<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'body',
        'is_approved',
        'app_user_id'
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
}
