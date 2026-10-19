@extends('layouts.app')

@section('content')


<div class="max-w-4xl mx-auto space-y-8">

    <!-- عنوان صفحه -->
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        جزئیات مال و ثبت کامنت
    </h2>

    <!-- کارت اطلاعات مال -->
    <div class="bg-white shadow-md rounded-lg p-6 border">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">
            اطلاعات مال
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- عنوان مال -->
            <div>
                <label class="block text-sm text-gray-500 mb-1">عنوان مال</label>
                <p class="bg-gray-100 border rounded p-3">{{ $asset->title }}</p>
            </div>

            <!-- شماره اموال -->
            <div>
                <label class="block text-sm text-gray-500 mb-1">شماره اموال</label>
                <p class="bg-gray-100 border rounded p-3">{{ $asset->asset_number }}</p>
            </div>

            <!-- ستاد -->
            <div>
                <label class="block text-sm text-gray-500 mb-1">ستاد</label>
                <p class="bg-gray-100 border rounded p-3">{{ $asset->city }}</p>
            </div>

            <!-- تاریخ بروزرسانی -->
            <div>
                <label class="block text-sm text-gray-500 mb-1">تاریخ بروزرسانی</label>
                <p class="bg-gray-100 border rounded p-3">
                    {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($asset->updated_at_date))->format('Y/m/d') }}
                </p>
            </div>
        </div>
    </div>

    <!-- تاریخچه کامنت‌ها -->
    <div class="bg-white shadow-md rounded-lg p-6 border">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">
            تاریخچه کامنت‌ها
        </h3>

        @if($asset->comments->count() > 0)
            <ul class="space-y-4">
                @foreach($asset->comments as $comment)
                    <li class="border rounded p-4 bg-gray-50">
                        <div class="flex justify-between mb-2">
                            <span class="font-bold text-gray-800">{{ $comment->user->name }}</span>
                            <span class="text-sm text-gray-500">
                                {{ $comment->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $comment->comment }}
                        </p>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">هنوز کامنتی ثبت نشده است.</p>
        @endif
    </div>

    <!-- فرم ثبت کامنت جدید -->
    <div class="bg-white shadow-md rounded-lg p-6 border">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">
            افزودن کامنت جدید
        </h3>

        <form action="{{ route('comments.store', [$asset]) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="comment_text" class="block text-sm text-gray-600 mb-1">کامنت شما</label>
                <textarea 
                    name="comment" 
                    id="comment_text"
                    rows="5"
                    class="w-full border rounded p-3 focus:ring focus:ring-blue-300"
                    required>{{ old('comment') }}</textarea>

                @error('comment')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- دکمه‌ها -->
            <div class="flex items-center space-x-3 space-x-reverse mt-4">

                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
                    ثبت کامنت
                </button>

                <a href="{{ route('assets.index') }}"
                   class="bg-gray-300 hover:bg-gray-400 text-black px-6 py-2 rounded shadow">
                   بازگشت
                </a>

            </div>
        </form>
    </div>

</div>

@endsection
