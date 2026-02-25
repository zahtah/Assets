<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;
use App\Models\AssetDuplicateAlert;
use App\Exports\AssetsExport;
// use Maatwebsite\Excel\Facades\Excel;


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
        $newAlertsCount = AssetDuplicateAlert::where('is_read', false)->count();
        return view('admin.users', compact('users','newAlertsCount'));
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
    //////////////////////////////////////////////////
    // فرم افزودن مال
    public function createUserAsset(User $user)
    {
        return view('admin.create-user-asset', compact('user'));
    }

    //////////////////////////////////////////////////
    // ذخیره مال
    public function storeUserAsset(Request $request, User $user)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'asset_number' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'updated_at_date' => 'required|date',
        ]);
        $updated_at = CalendarUtils::createCarbonFromFormat('Y/m/d', $request->updated_at_date);
        $user->assets()->create([
            'title' => $request->title,
            'asset_number' => $request->asset_number,
            'city' => $request->city,
            'updated_at_date' => $updated_at,
        ]);

        return redirect()
            ->route('admin.users.showUserAssets', $user)
            ->with('success','مال جدید ثبت شد');
    }
    public function alerts()
    {
        $alerts = AssetDuplicateAlert::with(['newUser','originalUser'])
                    ->latest()
                    ->get();

        return view('admin.alerts', compact('alerts'));
    }

    public function markAlertsRead()
    {
        AssetDuplicateAlert::where('is_read', false)
            ->update(['is_read' => true]);

        return redirect()->route('admin.alerts.index');
    }
    
    // public function exportAssets()
    // {
    //     return Excel::download(new AssetsExport, 'all-assets.xlsx');
    // }
}