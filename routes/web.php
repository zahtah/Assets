<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {

    if (!Auth::check()) {
        return redirect()->route('login');
    }
    $user = Auth::user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.users');
    }

    return redirect()->route('assets.index');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    //Route::resource('assets', AssetController::class);
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::resource('assets', AssetController::class)->except(['index']);
});

// ادمین
Route::middleware(['auth','is_admin'])->group(function () {

    Route::get('/admin/users', [AdminController::class,'users'])
        ->name('admin.users');

    Route::get('/admin/users/{user}', [AdminController::class,'showUserAssets'])
        ->name('admin.users.showUserAssets');

    // ویرایش مال
    Route::get('/admin/users/{user}/assets/{asset}/edit', [AdminController::class, 'editAsset'])
        ->name('admin.users.assets.edit');

    Route::put('/admin/users/{user}/assets/{asset}', [AdminController::class, 'updateAsset'])
        ->name('admin.users.assets.update');

    Route::delete('/admin/users/{user}/assets/{asset}', [AdminController::class, 'destroyAsset'])
        ->name('admin.users.assets.destroy');
    // فرم افزودن مال برای کاربر
    Route::get('/admin/users/{user}/assets/create', [AdminController::class, 'createUserAsset'])
        ->name('admin.users.assets.create');

    // ذخیره مال
    Route::post('/admin/users/{user}/assets', [AdminController::class, 'storeUserAsset'])
        ->name('admin.users.assets.store');

    Route::get('alerts', [AdminController::class, 'alerts'])
    ->name('admin.alerts.index');

    Route::post('alerts/mark-as-read', [AdminController::class, 'markAlertsRead'])
    ->name('admin.alerts.markRead');

    // Route::get('/admin/export-assets', [AdminController::class, 'exportAssets'])
    // ->middleware(['auth'])
    // ->name('admin.export.assets');
});

require __DIR__.'/auth.php';
