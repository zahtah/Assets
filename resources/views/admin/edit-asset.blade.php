@extends('layouts.app')

@section('content')

<h2 class="text-xl font-bold mb-4">ویرایش مال {{ $user->name }}</h2>

<form method="POST" action="{{ route('admin.users.assets.update', [$user,$asset]) }}" class="bg-white p-6 rounded shadow">
    @csrf
    @method('PUT')

    {{-- عنوان --}}
    <div class="mb-4">
        <label class="block mb-1">عنوان مال</label>
        <input type="text" name="title" value="{{ old('title', $asset->title) }}"
               class="w-full border p-2 rounded">
        @error('title')
            <div class="text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- شماره --}}
    <div class="mb-4">
        <label class="block mb-1">شماره اموال</label>
        <input type="text" name="asset_number" value="{{ old('asset_number', $asset->asset_number) }}"
               class="w-full border p-2 rounded">
        @error('asset_number')
            <div class="text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- ستاد --}}
    <div class="mb-4">
        <label class="block mb-1">ستاد</label>
        <select name="city" class="w-full border p-2 rounded">
            @php $cities = ['مرکزی','سمنان','شاهرود','دامغان','گرمسار','میامی','مهدیشهر']; @endphp
            @foreach($cities as $city)
                <option value="{{ $city }}" {{ old('city', $asset->city) == $city ? 'selected' : '' }}>
                    {{ $city }}
                </option>
            @endforeach
        </select>
        @error('city')
            <div class="text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- تاریخ --}}
    <div class="mb-4">
        <label class="block mb-1">تاریخ بروزرسانی</label>
        <input type="text" name="updated_at_date" id="updated_at_date"
            class="w-full border p-2 rounded"
            value="{{ old('updated_at_date', \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($asset->updated_at_date))->format('Y/m/d') ?? '') }}">
        @error('updated_at_date')
            <div class="text-red-600 mt-1">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">بروزرسانی</button>
    <a href="{{ route('admin.users.showUserAssets', $user) }}" class="ml-2 bg-gray-300 text-black px-4 py-2 rounded">انصراف</a>
</form>

{{-- حذف مال --}}
<form method="POST" action="{{ route('admin.users.assets.destroy', [$user,$asset]) }}" class="mt-4">
    @csrf
    @method('DELETE')
    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded"
        onclick="return confirm('آیا مطمئن هستید که می‌خواهید این مال را حذف کنید؟')">
        حذف مال
    </button>
</form>

@endsection