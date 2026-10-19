<?php

namespace App\Models;

use App\Support\Jalali;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class Asset extends Model
{
    //
    /** فیلدهایی که تغییرشان «ویرایش مال» حساب می‌شود */
    public const TRACKED_FIELDS = ['title', 'asset_number', 'count', 'description'];

    protected $fillable = [
        'user_id',
        'title',
        'asset_number',
        'count',
        'description',
        'city',
        'updated_at_date',
        'user_approved',
        'admin_approved',
        'comment',
        'admin_new_comment',
        'user_new_comment',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // اگر رابطه با User هم دارید (که معمولاً دارید)
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // فرض می کنیم ستون user_id در جدول assets است
    }

    /** تاریخچه‌ی تغییرات (برای مرتب‌سازی از orderByDesc('id') در محل استفاده کنید) */
    public function edits(): HasMany
    {
        return $this->hasMany(AssetEdit::class);
    }

    /** اموال بدون مالک = موجود در انبار */
    public function scopeUnowned($query)
    {
        return $query->whereNull($this->getTable() . '.user_id');
    }

    public function isInWarehouse(): bool
    {
        return $this->user_id === null;
    }

    /**
     * اعمال ویرایش مشخصات مال.
     *
     * - فقط در صورت تغییر واقعی ذخیره می‌شود.
     * - تاریخ بروزرسانی (updated_at_date) خودکار برابر امروز (وقت تهران) می‌شود،
     *   مگر اینکه ادمین عمداً تاریخ دیگری وارد کرده باشد.
     * - تاییدهای کاربر/ادمین ریست می‌شوند تا مجدداً تایید شوند.
     * - تغییرات در جدول asset_edits ثبت می‌شود.
     *
     * @return array<string, array{0: mixed, 1: mixed}> تغییرات (خالی = تغییری نبوده)
     */
    public function applyEdit(array $data, ?int $editorId, ?string $manualDate = null): array
    {
        $this->fill(Arr::only($data, self::TRACKED_FIELDS));

        $changes = [];
        foreach (self::TRACKED_FIELDS as $field) {
            $old = $this->getOriginal($field);
            $new = $this->getAttribute($field);
            if ((string) $old !== (string) $new) {
                $changes[$field] = [$old, $new];
            }
        }

        $oldDate = $this->getOriginal('updated_at_date')
            ? Carbon::parse($this->getOriginal('updated_at_date'))->toDateString()
            : null;
        $manual = $manualDate ? Jalali::parse($manualDate)?->toDateString() : null;
        $manualChanged = $manual !== null && $manual !== $oldDate;

        if (!$changes && !$manualChanged) {
            return [];
        }

        $newDate = $manualChanged ? $manual : Jalali::today();
        $this->updated_at_date = $newDate;
        if ($newDate !== $oldDate) {
            $changes['updated_at_date'] = [$oldDate, $newDate];
        }

        $this->user_approved = false;  // با هر بار به روزرسانی باید مجددا تایید گردد
        $this->admin_approved = false;
        $this->save();

        AssetEdit::create([
            'asset_id'          => $this->id,
            'editor_id'         => $editorId,
            'owner_id'          => $this->user_id,
            'previous_owner_id' => $this->user_id,
            'action'            => AssetEdit::ACTION_UPDATE,
            'changes'           => $changes,
        ]);

        return $changes;
    }

    /** خارج کردن از مالکیت کاربر و انتقال به انبار (بدون مالک) */
    public function releaseToWarehouse(?int $editorId): void
    {
        $previousOwner = $this->user_id;

        $this->user_id = null;
        $this->user_approved = false;
        $this->admin_approved = false;
        $this->updated_at_date = Jalali::today();
        $this->save();

        AssetEdit::create([
            'asset_id'          => $this->id,
            'editor_id'         => $editorId,
            'owner_id'          => null,
            'previous_owner_id' => $previousOwner,
            'action'            => AssetEdit::ACTION_RELEASE,
        ]);
    }

    /** اختصاص مالِ بدون مالک به یک کاربر */
    public function assignTo(User $user, ?int $editorId): void
    {
        $previousOwner = $this->user_id;

        $this->user_id = $user->id;
        $this->user_approved = false;
        $this->admin_approved = false;
        $this->updated_at_date = Jalali::today();
        $this->save();

        AssetEdit::create([
            'asset_id'          => $this->id,
            'editor_id'         => $editorId,
            'owner_id'          => $user->id,
            'previous_owner_id' => $previousOwner,
            'action'            => AssetEdit::ACTION_ASSIGN,
        ]);
    }
}
