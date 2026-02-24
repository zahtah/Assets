<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'hostname',
        'action',
    ];

    ////////////////////////////////////////
    // رابطه با کاربر
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}