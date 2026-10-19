<?php

namespace App\Support;

use Carbon\Carbon;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

/**
 * ابزارهای کمکی تاریخ شمسی و منطقه زمانی تهران.
 * در دیتابیس همه‌ی زمان‌ها به UTC ذخیره می‌شوند و فقط هنگام نمایش به وقت تهران تبدیل می‌شوند.
 */
class Jalali
{
    public const TZ = 'Asia/Tehran';

    /** تبدیل ارقام فارسی/عربی به انگلیسی */
    public static function normalizeDigits(string $s): string
    {
        return strtr($s, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /**
     * تبدیل رشته‌ی تاریخ شمسی (1405/07/21) به Carbon.
     * اگر سال >= 1700 باشد، میلادی در نظر گرفته می‌شود. در صورت نامعتبر بودن null برمی‌گرداند.
     */
    public static function parse(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = str_replace(['-', '.'], '/', self::normalizeDigits(trim($value)));

        if (!preg_match('#^(\d{4})/(\d{1,2})/(\d{1,2})$#', $value, $m)) {
            return null;
        }

        try {
            if ((int) $m[1] >= 1700) {
                return Carbon::createFromFormat('Y/m/d', sprintf('%04d/%02d/%02d', $m[1], $m[2], $m[3]))->startOfDay();
            }

            return CalendarUtils::createCarbonFromFormat('Y/m/d', sprintf('%04d/%02d/%02d', $m[1], $m[2], $m[3]))->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** امروز (میلادی، Y-m-d) به وقت تهران؛ برای ستون updated_at_date */
    public static function today(): string
    {
        return Carbon::now(self::TZ)->toDateString();
    }

    /** نمایش تاریخ (ستون date) به شمسی: 1405/07/21 */
    public static function date($value): string
    {
        if (empty($value)) {
            return '—';
        }

        return Jalalian::fromCarbon(Carbon::parse($value))->format('Y/m/d');
    }

    /** نمایش زمان (UTC در دیتابیس) به شمسی و وقت تهران: 1405/07/21 14:35 */
    public static function dateTime($value): string
    {
        if (empty($value)) {
            return '—';
        }

        $c = Carbon::parse($value)->timezone(self::TZ);

        return Jalalian::fromCarbon($c)->format('Y/m/d H:i');
    }

    /** شروع روز شمسی داده‌شده، به UTC (برای فیلتر created_at) */
    public static function dayStartUtc(?string $value): ?Carbon
    {
        $d = self::parse($value);

        return $d ? Carbon::parse($d->toDateString() . ' 00:00:00', self::TZ)->utc() : null;
    }

    /** پایان روز شمسی داده‌شده، به UTC */
    public static function dayEndUtc(?string $value): ?Carbon
    {
        $d = self::parse($value);

        return $d ? Carbon::parse($d->toDateString() . ' 23:59:59', self::TZ)->utc() : null;
    }
}
