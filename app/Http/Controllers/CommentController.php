<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Asset $asset) // از Route Model Binding برای Asset استفاده می کنیم
    {
        // 1. اعتبارسنجی درخواست
        $request->validate([
            'comment' => 'required|string|max:5000', // حداکثر 5000 کاراکتر برای کامنت
        ]);

        // 2. گرفتن کاربر فعلی
        $user = Auth::user();

        // 3. ایجاد کامنت جدید
        $comment = new Comment();
        $comment->asset_id = $asset->id;
        $comment->user_id = $user->id;
        $comment->comment = $request->input('comment'); // یا $request->comment
        $comment->save();

        // روش جایگزین (استفاده از create و fillable model)
        /*
        Comment::create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'comment' => $request->input('comment'),
        ]);
        */

         // کاربر کامنت گذاشته → ادمین باید بداند
        if ($user->role!='admin') {
            $asset->update(['admin_new_comment' => 1]);
            $user->new_comment = 1;
            $user->save();
        }
        // ادمین کامنت گذاشته → کاربر باید بداند
        if ($user->role=='admin') {
            $asset->update(['user_new_comment' => 1]);
        }

        // 4. بازگشت به صفحه مربوطه (مثلا صفحه جزئیات Asset یا صفحه لیست Assets کاربر)
        // فرض می کنیم می خواهید به صفحه نمایش دارایی های کاربر فعلی برگردید
        return back()->with('success', 'کامنت با موفقیت اضافه شد.');

    }
}
