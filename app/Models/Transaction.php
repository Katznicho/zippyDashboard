<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'app_user_id',
        'agent_id',
        'type',
        'amount',
        'status',
        'description',
        'reference',
        'payment_id',
        'owner_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appUser()
    {
        return $this->belongsTo(User::class, 'app_user_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
