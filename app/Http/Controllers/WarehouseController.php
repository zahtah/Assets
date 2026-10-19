<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetEdit;
use App\Support\Jalali;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * واحد انبار:
 *  - جستجوی مال با شماره اموال و خارج‌کردن آن از مالکیت کاربر (انتقال به انبار)
 *  - نمایش اموال بدون مالک و اختصاص آن‌ها به یک کاربر
 */
class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        // بخش ۱: جستجو با شماره اموال
        $q = trim((string) $request->query('q', ''));
        $found = collect();
        if ($q !== '') {
            $found = Asset::with('user')
                ->where('asset_number', 'LIKE', '%' . $q . '%')
                ->orderBy('asset_number')
                ->limit(100)
                ->get();
        }

        // بخش ۲: اموال بدون مالک (موجود در انبار) با فیلتر عنوان / شماره
        $title  = trim((string) $request->query('title', ''));
        $number = trim((string) $request->query('number', ''));

        $unowned = Asset::unowned()
            ->when($title !== '', fn ($query) => $query->where('title', 'LIKE', '%' . $title . '%'))
            ->when($number !== '', fn ($query) => $query->where('asset_number', 'LIKE', '%' . $number . '%'))
            ->orderBy('title')
            ->orderBy('asset_number')
            ->paginate(50)
            ->withQueryString();

        // کاربرانی که می‌توان مال را به آن‌ها اختصاص داد (نه ادمین و نه انبار)
        $users = User::where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'department', 'room_number']);

        return view('warehouse.index', compact('q', 'found', 'title', 'number', 'unowned', 'users'));
    }

    /**
     * جستجوی اموال (ادمین و انبار): با شماره اموال یا ویژگی‌های دیگر
     * و نمایش اینکه مال وجود دارد یا نه و در اختیار چه کسی است.
     */
    public function search(Request $request)
    {
        $q      = trim((string) $request->query('q', ''));
        $owner  = trim((string) $request->query('owner', ''));
        $unit   = trim((string) $request->query('unit', ''));
        $status = (string) $request->query('status', '');

        $searched = $q !== '' || $owner !== '' || $unit !== '' || in_array($status, ['owned', 'unowned'], true);

        $assets = null;
        if ($searched) {
            $query = Asset::with('user');

            // متن آزاد: هر کلمه باید در شماره، عنوان یا توضیحات بیاید (کلمات با AND، فیلدها با OR)
            foreach (preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $variants = self::textVariants($word);
                $query->where(function ($w) use ($variants) {
                    foreach ($variants as $v) {
                        $like = '%' . $v . '%';
                        $w->orWhere('asset_number', 'LIKE', $like)
                          ->orWhere('title', 'LIKE', $like)
                          ->orWhere('description', 'LIKE', $like);
                    }
                });
            }

            if ($status === 'unowned') {
                $query->whereNull('user_id');
            } elseif ($status === 'owned') {
                $query->whereNotNull('user_id');
            }

            if ($owner !== '') {
                $query->whereHas('user', function ($u) use ($owner) {
                    $u->where(function ($w) use ($owner) {
                        foreach (self::textVariants($owner) as $v) {
                            $w->orWhere('name', 'LIKE', '%' . $v . '%');
                        }
                    });
                });
            }

            if ($unit !== '') {
                $query->whereHas('user', fn ($u) => $u->where('unit', $unit));
            }

            $assets = $query->orderBy('asset_number')->paginate(25)->withQueryString();
        }

        return view('warehouse.search', compact('q', 'owner', 'unit', 'status', 'searched', 'assets'));
    }

    /** نسخه‌های مختلف یک عبارت: ارقام فارسی/عربی/انگلیسی و ی/ک عربی و فارسی */
    private static function textVariants(string $text): array
    {
        $digits = Jalali::normalizeDigits($text);
        $fa = str_replace(['ي', 'ك'], ['ی', 'ک'], $digits);
        $ar = str_replace(['ی', 'ک'], ['ي', 'ك'], $digits);

        return array_values(array_unique([$text, $digits, $fa, $ar]));
    }

    /** فرم ثبت مال جدید بدون مالک (در انبار) */
    public function create()
    {
        return view('warehouse.create');
    }

    /** ثبت مال جدید بدون مالک؛ مال مستقیماً در انبار قرار می‌گیرد */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'asset_number' => 'required|string|max:100|unique:assets,asset_number',
            'count'        => 'nullable|integer|min:1',
            'description'  => 'nullable|string|max:100',
        ], [
            'asset_number.unique' => 'مالی با این شماره اموال قبلاً ثبت شده است. از بخش جستجوی انبار آن را پیدا کنید.',
        ]);

        $asset = DB::transaction(function () use ($data, $request) {
            $asset = Asset::create([
                'user_id'         => null,
                'title'           => $data['title'],
                'asset_number'    => $data['asset_number'],
                'count'           => $data['count'] ?? 1,
                'description'     => $data['description'] ?? null,
                'city'            => '',
                'updated_at_date' => Jalali::today(),
                'user_approved'   => false,
                'admin_approved'  => false,
            ]);

            AssetEdit::create([
                'asset_id'  => $asset->id,
                'editor_id' => $request->user()->id,
                'owner_id'  => null,
                'action'    => AssetEdit::ACTION_CREATE,
            ]);

            return $asset;
        });

        // اگر «ثبت و افزودن مال بعدی» زده شد، به همان فرم برمی‌گردد
        $target = $request->boolean('add_another')
            ? route('warehouse.assets.create')
            : route('warehouse.index', ['number' => $asset->asset_number]);

        return redirect($target)->with('success', "مال «{$asset->title}» به شماره {$asset->asset_number} در انبار (بدون مالک) ثبت شد.");
    }

    /** خارج کردن مال از مالکیت کاربر → مال در انبار و بدون کاربر قرار می‌گیرد */
    public function release(Request $request, Asset $asset)
    {
        if ($asset->user_id === null) {
            return back()->with('error', 'این مال هم‌اکنون در انبار است و مالکی ندارد.');
        }

        $ownerName = $asset->user?->name ?? '—';

        DB::transaction(function () use ($asset, $request) {
            $asset->releaseToWarehouse($request->user()->id);
        });

        return redirect()
            ->route('warehouse.index', array_filter(['q' => $request->input('q')]))
            ->with('success', "مال «{$asset->title}» به شماره {$asset->asset_number} از مالکیت «{$ownerName}» خارج و به انبار منتقل شد.");
    }

    /** اختصاص یک یا چند مالِ بدون مالک به یک کاربر */
    public function assign(Request $request)
    {
        $data = $request->validate([
            'asset_ids'   => 'required|array|min:1',
            'asset_ids.*' => 'integer|exists:assets,id',
            'user_id'     => 'required|integer|exists:users,id',
        ], [
            'asset_ids.required' => 'حداقل یک مال را انتخاب کنید.',
            'asset_ids.min'      => 'حداقل یک مال را انتخاب کنید.',
            'user_id.required'   => 'کاربر مورد نظر را انتخاب کنید.',
        ]);

        $target = User::findOrFail($data['user_id']);
        if ($target->role !== 'user') {
            return back()->withErrors(['user_id' => 'مال فقط به کاربران عادی قابل اختصاص است.'])->withInput();
        }

        $assigned = 0;
        DB::transaction(function () use ($data, $target, $request, &$assigned) {
            // فقط اموالی که هنوز بدون مالک هستند (جلوگیری از تداخل با کاربر دیگر)
            $assets = Asset::unowned()->whereIn('id', $data['asset_ids'])->lockForUpdate()->get();
            foreach ($assets as $asset) {
                $asset->assignTo($target, $request->user()->id);
                $assigned++;
            }
        });

        $skipped = count($data['asset_ids']) - $assigned;
        $message = "{$assigned} مال به «{$target->name}» اختصاص داده شد.";
        if ($skipped > 0) {
            $message .= " ({$skipped} مورد قبلاً به کسی اختصاص یافته بود و تغییری نکرد.)";
        }

        return back()->with($assigned > 0 ? 'success' : 'error', $message);
    }
}
