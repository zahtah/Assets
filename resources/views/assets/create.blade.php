@extends('layouts.app')

@section('content')

<h2 class="text-xl font-bold mb-4">ثبت مال جدید</h2>

<form method="POST" action="{{ route('assets.store') }}"
      class="bg-white p-6 rounded shadow">

    @csrf

    <div class="mb-4">
        <label>عنوان مال</label>
        <input type="text" name="title"
               class="w-full border p-2 rounded"
               value="{{ old('title') }}">
        @error('title')
            <div class="text-red-600">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label>شماره اموال</label>
        <input type="text" name="asset_number"
               class="w-full border p-2 rounded">
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

    <button class="bg-green-500 text-white px-4 py-2 rounded">
        ثبت
    </button>

</form>

@endsection