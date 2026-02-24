<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;

class AdminController extends Controller
{
    // فقط ادمین ها اجازه دارند
    // public function __construct()
    // {
    //     $this->middleware(['auth','is_admin']);
    // }

    // نمایش لیست کاربران
    public function users()
    {
        // $users = User::all();
        // return view('admin.users', compact('users'));
        $users = User::with('latestLog')->paginate(10);

        return view('admin.users', compact('users'));
    }

    // نمایش لیست اموال یک کاربر
    public function showUserAssets(User $user)
    {
        $assets = $user->assets;
        return view('admin.user-assets', compact('user','assets'));
    }

    public function editAsset(User $user, Asset $asset)
    {
        // بررسی اینکه مال متعلق به همین کاربر است
        if ($asset->user_id !== $user->id) {
            abort(403);
        }

        return view('admin.edit-asset', compact('user','asset'));
    }

    public function updateAsset(Request $request, User $user, Asset $asset)
    {
        if ($asset->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'asset_number' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'updated_at_date' => 'required|date',
        ]);

        $updated_at = CalendarUtils::createCarbonFromFormat('Y/m/d', $request->updated_at_date);

        $asset->update([
            'title' => $request->title,
            'asset_number' => $request->asset_number,
            'city' => $request->city,
            'updated_at_date' => $updated_at,
        ]);

        return redirect()->route('admin.users.showUserAssets', $user)
                        ->with('success', 'مال با موفقیت بروزرسانی شد.');
    }

    public function destroyAsset(User $user, Asset $asset)
    {
        if ($asset->user_id !== $user->id) {
            abort(403);
        }

        $asset->delete();

        return redirect()->route('admin.users.showUserAssets', $user)
                        ->with('success', 'مال با موفقیت حذف شد.');
    }
}