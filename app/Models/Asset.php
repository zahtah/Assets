<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    //
    protected $fillable = [
        'user_id',
        'title',
        'asset_number',
        'city',
        'updated_at_date',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
