<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /** گزینه‌های طبقه */
    public const FLOORS = ['زیرزمین', 'همکف', 'اول', 'دوم'];

    /** نقش‌هایی که ادمین می‌تواند هنگام ساخت/ویرایش کاربر انتخاب کند */
    public const ASSIGNABLE_ROLES = [
        'user'      => 'کاربر عادی',
        'warehouse' => 'واحد انبار',
    ];

    protected $fillable = [
        'name',
        'email',
        'department',
        'unit',
        'room_number',
        'floor',
        'allow_editing',
        'password',
        'new_comment',
        'show_contract_button', // اضافه شد
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }
    // آخرین لاگ
    public function latestLog()
    {
        return $this->hasOne(UserActivityLog::class)->latestOfMany();
    }
    // آخرین ورود به سیستم (نه هر فعالیتی)
    public function latestLogin()
    {
        return $this->hasOne(UserActivityLog::class)->ofMany(
            ['id' => 'max'],
            function ($query) {
                $query->where('action', UserActivityLog::ACTION_LOGIN);
            }
        );
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isWarehouse(): bool
    {
        return $this->role === 'warehouse';
    }
    /** صفحه‌ی اصلی هر نقش بعد از ورود */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin'     => 'all-users',
            'warehouse' => 'warehouse.index',
            default     => 'assets.index',
        };
    }
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
