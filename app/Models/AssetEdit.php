<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تاریخچه‌ی تغییرات اموال (ویرایش مشخصات، خارج‌کردن از مالکیت، اختصاص به کاربر)
 */
class AssetEdit extends Model
{
    public const ACTION_UPDATE  = 'update';
    public const ACTION_RELEASE = 'release';
    public const ACTION_ASSIGN  = 'assign';
    public const ACTION_CREATE  = 'create';

    public const ACTION_LABELS = [
        self::ACTION_UPDATE  => 'ویرایش مشخصات',
        self::ACTION_RELEASE => 'خروج از مالکیت (انتقال به انبار)',
        self::ACTION_ASSIGN  => 'اختصاص به کاربر',
        self::ACTION_CREATE  => 'ثبت مال جدید در انبار',
    ];

    public const FIELD_LABELS = [
        'title'           => 'عنوان',
        'asset_number'    => 'شماره اموال',
        'count'           => 'تعداد',
        'description'     => 'توضیحات',
        'updated_at_date' => 'تاریخ بروزرسانی',
    ];

    protected $fillable = [
        'asset_id',
        'editor_id',
        'owner_id',
        'previous_owner_id',
        'action',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    /** مالک بعد از این تغییر (null = انبار) */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** مالک قبل از این تغییر */
    public function previousOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_owner_id');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }
}
