<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersAssetsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            // ۱. ابتدا بررسی می‌کنیم آیا کاربری با این ایمیل وجود دارد یا نه
            $user = User::firstOrCreate(
                [
                    'email' => $row['email'],   // معیار یکتایی
                ],
                [ // فقط وقتی کاربر تازه ساخته می‌شود، این مقادیر وارد می‌شوند
                    'name'        => $row['name'],
                    'department'  => $row['department'] ?? null,
                    'unit'        => $row['unit'] ?? null,
                    'room_number' => $row['room_number'] ?? null,
                    'floor'       => $row['floor'] ?? null,
                    'allow_editing' => true,
                    'password'    => bcrypt('12345678'),
                ]
            );

            // ۲. ثبت دارایی مربوط به کاربر (حتی اگر تکراری باشد)
            Asset::updateOrCreate(
                ['asset_number' => $row['asset_number']], // ستون شماره اموال
                [
                    'user_id'      => $user->id,
                    'title'        => $row['title'],
                    // 'asset_number' => $row['asset_number'],
                    'count'        => $row['count'],
                    'description'  => $row['description'],
                    'city'         => '',
                    'updated_at_date' => now(),
                ]
            );

            return $user;
        });
    }
}
