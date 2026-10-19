<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;
use Carbon\Carbon;
use App\Models\AssetDuplicateAlert;
use App\Exports\AssetsExport;
use App\Imports\UsersAssetsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\AssetEdit;
use App\Models\UserActivityLog;
use App\Support\Jalali;

class AdminController extends Controller
{
    // فقط ادمین ها اجازه دارند
    // public function __construct()
    // {
    //     $this->middleware(['auth','is_admin']);
    // }

    // نمایش لیست کاربران
    public function users(Request $request)
    {
        // $users = User::all();
        // return view('admin.users', compact('users'));
        $query = User::query();

        // اگر کاربر چیزی تایپ کرده بود، جستجو کن
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // $users = $query->with(['latestLog'])->get();
        // $users = User::with('latestLog',)->paginate(10);
        // لاگ ورود: آخرین «ورود به سیستم» هر کاربر (IP، نام سیستم و تاریخ)
        // کاربرانی که اخیراً وارد شده‌اند بالاتر نمایش داده می‌شوند
        $users = $query
            ->with('latestLogin')
            ->withMax(['activityLogs as last_login_at' => function ($q) {
                $q->where('action', UserActivityLog::ACTION_LOGIN);
            }], 'created_at')
            ->orderByDesc('last_login_at')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        $newAlertsCount = AssetDuplicateAlert::where('is_read', false)->count();
        return view('admin.users', compact('users','newAlertsCount'));
    }

    // نمایش لیست اموال یک کاربر
    public function showUserAssets(Request $request, User $user)
    {
        $assets = $user->assets()
            ->with([
                'comments' => fn ($q) => $q->with('user')->orderBy('id'),
                'edits'    => fn ($q) => $q->with(['editor', 'previousOwner', 'owner'])->orderByDesc('id'),
            ])
            ->get();

        // مالی که از صفحه‌ی «اموال ویرایش‌شده» انتخاب شده تا برجسته و باز نمایش داده شود
        $highlightId = (int) $request->query('highlight');

        return view('admin.user-assets', compact('user','assets','highlightId'));
    }

    // لیست کلیه اموالِ ویرایش‌شده‌ی همه‌ی کاربران (با فیلتر بازه‌ی زمانی)
    public function editedAssets(Request $request)
    {
        $from   = Jalali::dayStartUtc($request->input('from'));
        $to     = Jalali::dayEndUtc($request->input('to'));
        $action = $request->input('action');
        $userQ  = trim((string) $request->input('user'));
        $numQ   = trim((string) $request->input('number'));

        $invalidDates = ($request->filled('from') && !$from) || ($request->filled('to') && !$to);

        // شرط بازه‌ی زمانی/نوع تغییر روی تاریخچه
        $editsFilter = function ($q) use ($from, $to, $action) {
            if ($from) {
                $q->where('asset_edits.created_at', '>=', $from);
            }
            if ($to) {
                $q->where('asset_edits.created_at', '<=', $to);
            }
            if ($action && array_key_exists($action, AssetEdit::ACTION_LABELS)) {
                $q->where('asset_edits.action', $action);
            }
        };

        $query = Asset::query()
            ->whereHas('edits', $editsFilter)
            ->with('user')
            ->withCount(['edits as edits_in_range' => $editsFilter])
            ->withMax(['edits as last_edit_at' => $editsFilter], 'created_at')
            ->withCount('comments');

        if ($userQ !== '') {
            $query->whereHas('user', fn ($q) => $q->where('name', 'LIKE', '%' . $userQ . '%'));
        }
        if ($numQ !== '') {
            $query->where('asset_number', 'LIKE', '%' . $numQ . '%');
        }

        $assets = $query->orderByDesc('last_edit_at')->orderByDesc('assets.id')->paginate(20)->withQueryString();

        // آخرین تغییرِ هر مال (برای نمایش ویرایشگر و نوع تغییر)
        $lastEdits = AssetEdit::with(['editor', 'previousOwner'])
            ->whereIn('asset_id', $assets->pluck('id'))
            ->where($editsFilter)
            ->orderByDesc('id')
            ->get()
            ->groupBy('asset_id')
            ->map->first();

        $actionLabels = AssetEdit::ACTION_LABELS;

        return view('admin.edited-assets', compact('assets', 'lastEdits', 'actionLabels', 'invalidDates'));
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

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'asset_number' => 'required|string|max:255',
            'count' => 'nullable|integer|min:0',
            'description' => 'nullable|string|max:100',
            'updated_at_date' => 'nullable|string|max:20',
        ]);

        if (!empty($data['updated_at_date']) && !Jalali::parse($data['updated_at_date'])) {
            return back()->withInput()->withErrors(['updated_at_date' => 'تاریخ نامعتبر است. نمونه‌ی صحیح: 1405/07/21']);
        }

        // تاریخ بروزرسانی با هر تغییر واقعی، خودکار برابر امروز می‌شود
        // (مگر اینکه ادمین عمداً تاریخ دیگری وارد کرده باشد) و تغییرات در تاریخچه ثبت می‌شود
        $changes = $asset->applyEdit($data, $request->user()->id, $data['updated_at_date'] ?? null);

        return redirect()->route('admin.users.showUserAssets', [$user, 'highlight' => $asset->id])
                        ->with('success', $changes ? 'مال با موفقیت بروزرسانی شد.' : 'تغییری در اطلاعات مال ایجاد نشده بود.');
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

    //comment

    public function commentAsset(User $user, Asset $asset)
    {
        // بررسی اینکه مال متعلق به همین کاربر است
        if ($asset->user_id !== $user->id) {
            abort(403);
        }
        $asset->update([
            'admin_new_comment' => 0
        ]);
        $user->update([
            'new_comment' => 0
        ]);

        return view('admin.edit-comment', compact('user','asset'));
    }

    public function updateComment(Request $request, User $user, Asset $asset)
    {
        if ($asset->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'comment' => 'nullable|string',
        ]);
        //$comment= $user->name + ":" + $request->comment;
        $finalCommentContent =  $user->name . ":" . $request->comment;

        $asset->update([
            'comment' => $request->comment,
        ]);

        return redirect()->route('admin.users.showUserAssets', $user)
                        ->with('success', ';کامنت با موفقیت بروزرسانی شد.');
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
            'count' => 'int',
            'description' => 'string|max:100',
            'city' => 'string|max:100',
            'updated_at_date' => 'required|date',
        ]);
        $updated_at = CalendarUtils::createCarbonFromFormat('Y/m/d', $request->updated_at_date);
        $user->assets()->create([
            'title' => $request->title,
            'asset_number' => $request->asset_number,
            'count' => $request->count,
            'description' => $request->description,
            'city' => "",
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
    
    public function exportAssets()
    {
        return Excel::download(new AssetsExport, 'all-assets.xlsx');
    }

    public function allUsers(Request $request)
    {
        $usersQuery = User::query();

        // دریافت مقادیر جستجو از request
        $searchName = $request->input('search.name');
        $searchDepartment = $request->input('search.department');
        $searchUnit = $request->input('search.unit'); // این خط جدید است

        // اعمال فیلترها فقط در صورت پر بودن
        if (!empty($searchName)) {
            $usersQuery->where('name', 'LIKE', '%' . $searchName . '%');
        }
        if (!empty($searchDepartment)) {
            $usersQuery->where('department', 'LIKE', '%' . $searchDepartment . '%');
        }
        // شرط جدید برای فیلتر واحد
        if (!empty($searchUnit)) {
            $usersQuery->where('unit', '=', $searchUnit); // یا LIKE اگر می‌خواهید بخشی از نام واحد را جستجو کنید
        }

        // فیلتر برای حذف کاربران ادمین و کاربر لاگین شده
        $usersQuery->where('role', '!=', 'admin');
        $usersQuery->where('id', '!=', auth()->id());

        // نتیجه نهایی
        $users = $usersQuery->with('latestLog')->get(); // یا paginate(20)->withQueryString()
        $newAlertsCount = AssetDuplicateAlert::where('is_read', false)->count();

        return view('admin.users.all-users', compact('users','newAlertsCount'));
    }
    public function createUser()
    {
        return view('admin.users.create-user');
    }
    public function storeUser(Request $request){
        $data = $request -> validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','max:255','unique:users'],
            // 'phone' => ['string','max:255'],
            // 'address' => ['string','max:255'],
            'department' => ['nullable','string','max:255'],
            'unit' => ['nullable','string','max:255'],
            'room_number' => ['nullable','int'],
            'floor' => ['nullable','string', Rule::in(User::FLOORS)],
            'role' => ['nullable', Rule::in(array_keys(User::ASSIGNABLE_ROLES))],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        // نقش: کاربر عادی (پیش‌فرض) یا واحد انبار؛ role عمداً fillable نیست
        $role = $data['role'] ?? 'user';
        unset($data['role']);
        if ($role === 'warehouse' && empty($data['unit'])) {
            $data['unit'] = 'انبار';
        }

        $user = new User($data);
        $user->role = $role;
        $user->save();
        // if($request->has('verify')){
        //     $user->markEmailAsVerified();
        // }
        return redirect(route('all-users'));
    }
    public function editUser($id){
        $user = User::find($id);
        return view('admin.users.edit-user')->with('user',$user);
    }
    public function updateUser(Request $request , $id){
        $user = User::findOrFail($id);
        $data =$request -> validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'department' => ['nullable','string','max:255'],
            'unit' => ['nullable','string','max:255'],
            'room_number' => ['nullable','int'],
            // مقدار فعلی کاربر (اگر قبلاً چیز دیگری ثبت شده) هم مجاز می‌ماند تا اطلاعات قدیمی خراب نشود
            'floor' => ['nullable','string', Rule::in(array_merge(User::FLOORS, array_filter([$user->floor])))],
            'role' => ['nullable', Rule::in(array_keys(User::ASSIGNABLE_ROLES))],
        ]);
        $role = $data['role'] ?? null;
        unset($data['role']);
        if (!is_null($request->password)){
            $request -> validate([
            'password' => ['required','string','min:8','confirmed'],
            ]);
            $data['password']=$request->password;
        }
        $user->update($data);
        // نقش ادمین هرگز از اینجا تغییر نمی‌کند
        if ($role && $user->role !== 'admin') {
            $user->role = $role;
            $user->save();
        }
        // if($request->has('verify')){
        //     $user->markEmailAsVerified();
        // }
        return redirect(route('all-users'));
    }
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('all-users')->with('success', 'کاربر با موفقیت حذف شد!');
        } catch (\Exception $e) {
            return redirect()->route('all-users')->with('error', 'کاربر حذف نشد! خطا: ' . $e->getMessage());
        }
    }
    // متدی برای تغییر وضعیت فعال/غیرفعال ویرایش
    public function toggleEditing(User $user)
    {
        $user->allow_editing = !$user->allow_editing; // وضعیت را معکوس می کند
        $user->save();

        return back()->with('success', "کاربر '{$user->name}' تغییر مجوز با موفقیت انجام شد.");
    }
    public function toggleAdminApproval(Asset $asset)
    {
        $asset->update(['admin_approved' => !$asset->admin_approved]);
        return back()->with('success', 'وضعیت تایید ادمین تغییر یافت.');
    }

    /**
     * Show the form for bulk editing user permissions by department name.
     */
    public function showBulkEditPermissionsFormByName()
    {
        // دریافت تمام نام‌های دپارتمان منحصر به فرد از جدول users
        $unitNames = User::query()
            ->whereNotNull('unit') // اطمینان از اینکه فیلد department خالی نیست
            ->distinct('unit')
            ->pluck('unit'); // فقط ستون department را بگیر و به صورت آرایه برگردان

        return view('admin.users.bulk_edit_permissions', compact('unitNames'));
    }

    /**
     * Bulk update allow_editing for users in a specific department by name.
     */
    public function bulkAllowEditingByName(Request $request)
    {
        $request->validate([
            'unit_name' => 'required|string', // چون نام دپارتمان است، string باشد
            'action' => 'required|in:enable,disable',
        ]);

        $unitName = $request->input('unit_name');
        $action = $request->input('action');

        // مقدار جدید برای allow_editing
        $newValue = ($action === 'enable') ? true : false;

        try {
            DB::transaction(function () use ($unitName, $newValue) {
                // پیدا کردن تمام کاربرانی که department آنها با نام ورودی مطابقت دارد
                $users = User::where('unit', $unitName)->get(); // فیلتر بر اساس نام دپارتمان

                // اگر می‌خواهید مطمئن شوید که دپارتمان نامعتبر نیست (اختیاری)
                // if ($users->isEmpty()) {
                //     throw new \Exception("Department name not found or no users in this department.");
                // }

                foreach ($users as $user) {
                    $user->allow_editing = $newValue;
                    $user->save();
                }
            });

            return back()->with('success', "وضعیت ویرایش کاربران واحد '{$unitName}' با موفقیت به‌روزرسانی شد.");

        } catch (\Exception $e) {
           // \Log::error("Error during bulk update of user editing permissions by name: " . $e->getMessage());
            return back()->with('error', 'خطایی در به‌روزرسانی وضعیت کاربران رخ داد. لطفا دوباره تلاش کنید.');
        }
    }
    public function importUsersAssets(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new UsersAssetsImport, $request->file('excel_file'));

        return back()->with('success', 'اطلاعات کاربران و اموال با موفقیت وارد شدند.');
    }

    public function generateContract(User $user)
    {
        // بررسی مجوز (اختیاری)
        // if (!auth()->user()->isAdmin()) {
        //     abort(403, 'شما دسترسی ندارید.');
        // }


        // فیلتر کردن اموال: فقط آنهایی که user_approved و admin_approved هر دو true (یا 1) هستند
        // فرض می‌کنیم رابطه 'assets' در مدل User تعریف شده و هر Asset دارای user_id است
        $approvedAssets = $user->assets()
        ->where('user_approved', true)
        ->where('admin_approved', true)
        ->get(); // یا ->wherePivot(...) اگر از belongsToMany استفاده کردی

        // اگر از belongsToMany استفاده کردی و وضعیت تایید در جدول واسط بود:
        // $approvedAssets = $user->assets()
        //     ->wherePivot('user_approved', true)
        //     ->wherePivot('admin_approved', true)
        //     ->get();

        // حالا ویو را با لیست اموال تایید شده رندر کن
        $html = view('admin.users.contract-template', compact('user', 'approvedAssets'))->render();

        return response($html);
    }
    public function updateContractButtonStatus(Request $request, User $user)
    {
        // بررسی مجوز ادمین (اگر در middleware نباشد)
        // if (!auth()->user()->isAdmin()) {
        //     abort(403, 'شما دسترسی ندارید.');
        // }

        $user->show_contract_button = $request->has('show_contract_button'); // true اگر تیک خورده باشد، false اگر نه
        $user->save();

        return back()->with('success', 'وضعیت نمایش دکمه قرارداد به‌روزرسانی شد.');
    }


}