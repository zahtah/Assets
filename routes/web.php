<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// صفحه اصلی و ریدایرکت‌ها
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    $user = Auth::user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.users');
    }
    if ($user->role === 'warehouse') {
        return redirect()->route('warehouse.index');
    }
    return redirect()->route('assets.index');
});

// احراز هویت و پروفایل کاربر
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// مدیریت دارایی‌ها (Assets) برای کاربران عادی
// این گروه فقط به کاربران لاگین شده اجازه دسترسی می‌دهد
Route::middleware(['auth'])->group(function () {
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::resource('assets', AssetController::class)->except(['index']); // سایر عملیات AssetController

    // تایید/لغو تایید توسط کاربر عادی
    // این مسیر باید دقیقاً اینجا باشد و middleware مخصوص ادمین نداشته باشد
    Route::patch('/assets/{asset}/toggle-user-approval', [AssetController::class, 'toggleUserApproval'])
        ->name('assets.toggleUserApproval');

    Route::post('/assets/{asset}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/users/{user}/assets/{asset}', [AdminController::class, 'updateAsset'])->middleware('is_admin')->name('admin.users.assets.update');
    Route::get('/users/{user}/contract', [AdminController::class, 'generateContract'])->name('users.contract');
});

// مدیریت ادمین
// این گروه فقط به کاربران لاگین شده با نقش ادمین اجازه دسترسی می‌دهد
Route::middleware(['auth', 'is_admin'])->prefix('admin')->group(function () {

    // پنل ادمین کاربران
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/users/{user}', [AdminController::class, 'showUserAssets'])->name('admin.users.showUserAssets');

    // مدیریت دارایی‌ها توسط ادمین
    Route::get('/users/{user}/assets/{asset}/edit', [AdminController::class, 'editAsset'])->name('admin.users.assets.edit');
    
    Route::delete('/users/{user}/assets/{asset}', [AdminController::class, 'destroyAsset'])->name('admin.users.assets.destroy');
    Route::get('/users/{user}/assets/create', [AdminController::class, 'createUserAsset'])->name('admin.users.assets.create');
    Route::post('/users/{user}/assets', [AdminController::class, 'storeUserAsset'])->name('admin.users.assets.store');
    Route::get('/users/{user}/assets/{asset}/comment', [AdminController::class, 'commentAsset'])->name('admin.users.assets.comment');
    Route::put('/users/{user}/assets/{asset}', [AdminController::class, 'updateComment'])->name('admin.users.assets.updateComment');

    // تایید/لغو تایید توسط ادمین
    // توجه: این مسیر نیز باید داخل گروه ادمین باشد
    Route::patch('/assets/{asset}/toggle-admin-approval', [AdminController::class, 'toggleAdminApproval'])
        ->name('assets.toggleAdminApproval'); // نام روت را می‌توان به admin.assets.toggleAdminApproval تغییر داد برای وضوح بیشتر

    // مدیریت هشدارها
    Route::get('alerts', [AdminController::class, 'alerts'])->name('admin.alerts.index');
    Route::post('alerts/mark-as-read', [AdminController::class, 'markAlertsRead'])->name('admin.alerts.markRead');

    // لیست کلیه اموال ویرایش‌شده‌ی همه‌ی کاربران
    Route::get('/edited-assets', [AdminController::class, 'editedAssets'])->name('admin.edited-assets');

    // خروجی گرفتن دارایی‌ها
    Route::get('/export-assets', [AdminController::class, 'exportAssets'])->name('admin.export.assets');

    // مدیریت کاربران
    Route::get('/all-users', [AdminController::class, 'allUsers'])->name('all-users');
    Route::get('/create-user', [AdminController::class, 'createUser'])->name('create_user');
    Route::post('/store-user', [AdminController::class, 'storeUser'])->name('store-user');
    Route::get('/edit-user/{id}', [AdminController::class, 'editUser'])->name('edit-user');
    Route::put('/update-user/{id}', [AdminController::class, 'updateUser'])->name('update-user');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('delete-user');
    Route::patch('/admin/users/{user}/toggle-editing', [AdminController::class, 'toggleEditing'])->name('admin.users.toggleEditing');
    
    // روت برای آپدیت وضعیت دکمه قرارداد
    Route::patch('admin/users/{user}/update_contract_button', [AdminController::class, 'updateContractButtonStatus'])
        ->name('users.update_contract_button');


    // Bulk update for user editing permissions by department name
    Route::get('/users/bulk-edit-permissions', [AdminController::class, 'showBulkEditPermissionsFormByName'])
        ->name('admin.users.bulk_edit_permissions'); // route برای نمایش فرم

    Route::post('/users/bulk-allow-editing', [AdminController::class, 'bulkAllowEditingByName'])
        ->name('admin.users.bulk_allow_editing'); // route برای پردازش عملیات

    Route::post('/import-users-assets', [AdminController::class, 'importUsersAssets'])
    ->name('import.users.assets');


});

// واحد انبار (نقش warehouse و ادمین)
Route::middleware(['auth', 'is_warehouse'])->prefix('warehouse')->group(function () {
    Route::get('/', [WarehouseController::class, 'index'])->name('warehouse.index');
    Route::get('/search', [WarehouseController::class, 'search'])->name('warehouse.search');
    Route::get('/assets/create', [WarehouseController::class, 'create'])->name('warehouse.assets.create');
    Route::post('/assets', [WarehouseController::class, 'store'])->name('warehouse.assets.store');
    Route::post('/assets/{asset}/release', [WarehouseController::class, 'release'])->name('warehouse.release');
    Route::post('/assign', [WarehouseController::class, 'assign'])->name('warehouse.assign');
});

// فایل‌های احراز هویت لاراول
require __DIR__.'/auth.php';
