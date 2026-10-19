<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserActivityLog extends Model
{
    use HasFactory;

    public const ACTION_LOGIN = 'ورود به سیستم';

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

    /**
     * ثبت یک رویداد در لاگ کاربر (IP و نام سیستم از روی درخواست)
     */
    public static function record(User $user, Request $request, string $action): self
    {
        $ip = $request->ip();

        $hostname = 'نامشخص';
        try {
            $resolved = $ip ? gethostbyaddr($ip) : null;
            if ($resolved && $resolved !== $ip) {
                $hostname = $resolved;
            }
        } catch (\Throwable $e) {
            // نام سیستم مهم نیست؛ ثبت لاگ نباید ورود را مختل کند
        }

        return static::create([
            'user_id'    => $user->id,
            'ip_address' => $ip,
            'hostname'   => $hostname,
            'action'     => $action,
        ]);
    }
}
