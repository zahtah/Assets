<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDuplicateAlert extends Model
{
    protected $fillable = [
        'new_user_id',
        'original_user_id',
        'asset_number',
        'original_created_at',
        'is_read'
    ];

    public function newUser()
    {
        return $this->belongsTo(User::class, 'new_user_id');
    }

    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }
}
