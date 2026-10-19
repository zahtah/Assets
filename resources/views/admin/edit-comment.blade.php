@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto space-y-8">

    <!-- تاریخچه کامنت‌ها -->
    <div class="bg-white shadow-md rounded-lg p-6 border">
        <h3 class="text-xl font-bold text-gray-800 mb-5">
            تاریخچه کامنت‌ها
        </h3>

        @if($asset->comments->count() > 0)
            <ul class="space-y-5">
                @foreach($asset->comments as $comment)
                    <li class="border-b pb-5 last:border-b-0 last:pb-0">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-800 text-lg">
                                {{ $comment->user->name }}
                            </span>
                            <span class="text-sm text-gray-500">
                                {{ $comment->created_at->format('Y-m-d H:i') }}
                            </span>
                        </div>
                        <p class="text-gray-700 leading-relaxed text-base">
                            {{ $comment->comment }}
                        </p>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500 text-center py-8">
                هنوز کامنتی برای این مال ثبت نشده است.
            </p>
        @endif
    </div>

    <!-- فرم ثبت کامنت جدید -->
    <div class="bg-white shadow-md rounded-lg p-6 border">
        <h3 class="text-xl font-bold text-gray-800 mb-5">
            افزودن کامنت جدید
        </h3>

        <form action="{{ route('comments.store', $asset) }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="comment_text" class="block text-sm font-medium text-gray-700 mb-2">
                    کامنت شما
                </label>
                <textarea
                    name="comment"
                    id="comment_text"
                    rows="6"
                    class="w-full border border-gray-300 rounded-lg p-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-base placeholder-gray-400"
                    required
                    placeholder="نوشتن کامنت..."
                >{{ old('comment') }}</textarea>

                @error('comment')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- دکمه‌ها -->
            <div class="flex items-center justify-between pt-4">
                <button
                    type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 font-bold py-3 px-6 rounded-lg shadow-lg transition duration-300 ease-in-out">
                    ثبت کامنت
                </button>

                {{-- دکمه بازگشت به صفحه کاربر --}}
                {{-- اگر $user در دسترس نیست، از route('assets.index') یا route دیگری استفاده کنید --}}
                <a href="{{ route('admin.users.showUserAssets', $user) }}"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg shadow transition duration-300 ease-in-out">
                    بازگشت به صفحه کاربر
                </a>
            </div>
        </form>
    </div>

</div>

@endsection
