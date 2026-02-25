@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">

    <h2 class="text-2xl font-bold mb-6">
        افزودن مال برای {{ $user->name }}
    </h2>

    <form method="POST" action="{{ route('admin.users.assets.store', $user) }}">
        @csrf

        <div class="mb-4">
            <label class="block mb-1 font-medium">عنوان</label>
            <input type="text" name="title"
                   class="w-full border rounded px-3 py-2"
                   required>
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-medium">شماره مال</label>
            <input type="text" name="asset_number"
                   class="w-full border rounded px-3 py-2"
                   required>
        </div>

        <div class="mb-4">
            <label>ستاد</label>
            <select name="city" class="w-full border p-2 rounded">
                <option value="مرکزی">مرکزی</option>
                <option value="سمنان">سمنان</option>
                <option value="دامغان">دامغان</option>
                <option value="شاهرود">شاهرود</option>
                <option value="گرمسار">گرمسار</option>
                <option value="مهدیشهر">مهدیشهر</option>
                <option value="میامی">میامی</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block mb-1">تاریخ بروزرسانی</label>
            <input type="text" name="updated_at_date" id="updated_at_date"
                class="w-full border p-2 rounded"
                value="{{ old('updated_at_date', \Morilog\Jalali\Jalalian::now()->format('Y/m/d')) }}">
            @error('updated_at_date')
                <div class="text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex justify-between">
            <button type="submit"
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded">
                ذخیره
            </button>

            <a href="{{ route('admin.users.showUserAssets', $user) }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                انصراف
            </a>
        </div>

    </form>

</div>

@endsection