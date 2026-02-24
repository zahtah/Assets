<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
});

require __DIR__.'/auth.php';
