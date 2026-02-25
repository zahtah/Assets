<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Asset;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;
use App\Models\UserActivityLog;
use App\Models\AssetDuplicateAlert;

class AssetController extends Controller
{
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

        if ($existingAsset) {

            AssetDuplicateAlert::create([
                'new_user_id' => $request->user()->id,
                'original_user_id' => $existingAsset->user_id,
                'asset_number' => $request->asset_number,
                'original_created_at' => $existingAsset->created_at,
                'is_read' => false
            ]);
        }

        $request->validate([
            'title' => 'required',
            'asset_number' => 'required',
            'city' => 'required',
            'updated_at_date' => 'required|date',
        ]);

        $updated_at = CalendarUtils::createCarbonFromFormat('Y/m/d', $request->updated_at_date);

        Asset::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'asset_number' => $request->asset_number,
            'city' => $request->city,
            'updated_at_date' => $updated_at, // ذخیره به میلادی
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
    
}
