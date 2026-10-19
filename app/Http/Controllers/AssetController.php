<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Asset;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;
use App\Models\UserActivityLog;
use App\Models\AssetDuplicateAlert;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AssetController extends Controller
{
    use AuthorizesRequests;
    //
    public function index()
    {
        $assets = request()->user()->assets;

        return view('assets.index', compact('assets'));
    }
    // فرم ثبت مال جدید
    public function create()
    {
        return view('assets.create');
    }
    public function store(Request $request)
    {
        $existingAsset = Asset::where('asset_number', $request->asset_number)->first();

        // اگر مالِ قبلی در انبار (بدون مالک) باشد، مالک قبلی‌ای برای هشدار وجود ندارد
        if ($existingAsset && $existingAsset->user_id) {

            AssetDuplicateAlert::create([
                'new_user_id' => $request->user()->id,
                'original_user_id' => $existingAsset->user_id,
                'asset_number' => $request->asset_number,
                'count' => $request->count,
                'description' => $request->description,
                'original_created_at' => $existingAsset->created_at,
                'is_read' => false
            ]);
        }

        $request->validate([
            'title' => 'required',
            'asset_number' => 'required',
            'count' => 'required',
            'description' => 'required',
            'city' => '',
            'updated_at_date' => 'required|date',
        ]);

        $updated_at = CalendarUtils::createCarbonFromFormat('Y/m/d', $request->updated_at_date);

        Asset::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'asset_number' => $request->asset_number,
            'count' => $request->count,
            'description' => $request->description,
            'city' =>"",
            'updated_at_date' => $updated_at, // ذخیره به میلادی
            'user_approved' => false,
            'admin_approved' => false,
        ]);

         ////////////////////////////////////////////////////////
        // گرفتن اطلاعات سیستم کاربر
        $ip = $request->ip();
        $hostname = gethostbyaddr($ip);
        if ($hostname == $ip) {
            $hostname = 'نامشخص';
        }

        ////////////////////////////////////////////////////////
        // ذخیره لاگ
        $user = Auth::user();
        UserActivityLog::create([
            'user_id'   => $user->id,
            'ip_address'=> $ip,
            'hostname'  => $hostname,
            'action'    => 'ثبت مال جدید توسط کاربر',
        ]);

        return redirect()->route('assets.index')
                        ->with('success','با موفقیت ثبت شد');
    }
    public function edit(Request $request, Asset $asset)
    {
        if ($asset->user_id !== $request->user()->id) {
            abort(403);
        }
        $asset->update([
            'user_new_comment' => 0
        ]);

        return view('assets.edit', compact('asset'));
    }

    // بروزرسانی مال
    public function update(Request $request, Asset $asset)
    {
        if ($asset->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'asset_number' => 'required|string|max:255',
            'count' => 'required',
            'description' => 'required',
            'city' => '',
            'updated_at_date' => 'required|date',
            'comment' => 'nullable|string',
        ]);

        // تاریخ بروزرسانی با هر تغییر واقعی خودکار امروز می‌شود و تغییرات در تاریخچه ثبت می‌گردد
        $asset->comment = $request->comment;
        $asset->applyEdit($request->only(Asset::TRACKED_FIELDS), $request->user()->id, $request->updated_at_date);
        if ($asset->isDirty()) {
            $asset->save();
        }

        return redirect()->route('assets.index')
                         ->with('success', 'مال با موفقیت بروزرسانی شد.');
    }

    // حذف مال (اختیاری)
    public function destroy(Request $request, Asset $asset)
    {
        if ($asset->user_id !== $request->user()->id) {
            abort(403);
        }

        $asset->delete();

        return redirect()->route('assets.index')
                         ->with('success', 'مال با موفقیت حذف شد.');
    }
    public function toggleUserApproval(Asset $asset)
    {
        // فقط مالک بتواند
        //$this->authorize('update', $asset);

        $asset->update(['user_approved' => !$asset->user_approved]);

        return back()->with('success', 'وضعیت تایید شما تغییر یافت.');
    }
    
}
