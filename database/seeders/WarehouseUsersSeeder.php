<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ساخت کاربران واحد انبار (elahi و argashte)
 *
 *   php artisan db:seed --class=WarehouseUsersSeeder
 *
 * رمز عبور کاربرِ جدید تصادفی ساخته و در خروجی نمایش داده می‌شود.
 * اگر کاربر از قبل وجود داشته باشد فقط نقش او «انبار» می‌شود و رمزش تغییر نمی‌کند.
 */
class WarehouseUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            'elahi'    => 'الهی',
            'argashte' => 'ارگشته',
        ];

        foreach ($users as $username => $name) {
            $user = User::where('email', $username)->first();
            $password = null;

            if (!$user) {
                $password = Str::random(10);
                $user = new User([
                    'name'          => $name,
                    'email'         => $username,
                    'department'    => 'ستاد',
                    'unit'          => 'انبار',
                    'allow_editing' => false,
                    'password'      => $password,
                ]);
            }

            $user->role = 'warehouse';
            $user->save();

            $this->command?->info($password
                ? "ساخته شد: {$username} | رمز عبور: {$password}"
                : "از قبل وجود داشت؛ نقش «انبار» اعمال شد: {$username}");
        }
    }
}
