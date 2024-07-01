<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'body',
        'is_approved',
        'app_user_id',
        'rating'
    ];


    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(){
        return $this->belongsTo(Agent::class);
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
