@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10">

    {{-- Success message --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300">
            {{ session('success') }}
        </div>
    @endif

    <div class=" ">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">
             پروفایل کاربر
        </h2>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf

            {{-- نام --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700">نام</label>
                <input type="text" name="name"
                       value="{{ old('name', $user->name) }}"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" readonly>
            </div>

            {{-- ساختمان --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700">ساختمان</label>
                <input type="text" name="department"
                       value="{{ old('department', $user->department) }}"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" readonly>
            </div>
            {{-- <div>
                <label for="department" class="block text-gray-700 mb-1">ساختمان</label>
                <select id="department" name="department"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly>
                    @php
                        $current = old('department', $user->department);
                    @endphp
                    <option value="ستاد">ستاد</option>
                    <option value="امور منابع آب مهدیشهر">امور منابع آب مهدیشهر</option>
                    <option value="امور منابع آب سمنان">امور منابع آب سمنان</option>
                    <option value="امور منابع آب شاهرود">امور منابع آب شاهرود</option>
                    <option value="امور منابع آب دامغان">امور منابع آب دامغان</option>
                    <option value="امور منابع آب گرمسار">امور منابع آب گرمسار</option>
                    <option value="امور منابع آب میامی">امور منابع آب میامی</option>
                </select>
            </div> --}}

            {{-- واحد --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700">واحد</label>
                <input type="text" name="unit"
                       value="{{ old('unit', $user->unit) }}"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" readonly>
            </div>

            {{-- <div>
                <label for="unit" class="block text-gray-700 mb-1">واحد</label>
                <select id="unit" name="unit"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300" readonly>

                    @php
                        $current = old('unit', $user->unit);
                    @endphp

                    <option value="برنامه ریزی" {{ $current == 'برنامه ریزی' ? 'selected' : '' }}>برنامه ریزی</option>
                    <option value="حفاظت" {{ $current == 'حفاظت' ? 'selected' : '' }}>حفاظت</option>
                    <option value="IT" {{ $current == 'IT' ? 'selected' : '' }}>IT</option>
                    <option value="مالی" {{ $current == 'مالی' ? 'selected' : '' }}>مالی</option>
                    <option value="مدیریت" {{ $current == 'مدیریت' ? 'selected' : '' }}>مدیریت</option>
                    <option value="مطالعات" {{ $current == 'مطالعات' ? 'selected' : '' }}>مطالعات</option>
                    <option value="طرح و توسعه" {{ $current == 'طرح و توسعه' ? 'selected' : '' }}>طرح و توسعه</option>

                </select>
            </div> --}}

            {{-- شماره اتاق --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700">شماره اتاق</label>
                <input type="text" name="room_number"
                       value="{{ old('room_number', $user->room_number) }}"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" readonly>
            </div>

            {{-- طبقه --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700">طبقه</label>
                <input type="text" name="floor"
                       value="{{ old('floor', $user->floor) }}"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" readonly>
            </div>

            <hr class="my-8 border-gray-300">

            <h3 class="text-xl font-semibold text-gray-800 mb-4">
                تغییر رمز عبور (اختیاری)
            </h3>

            {{-- رمز جدید --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700">رمز جدید</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- تأیید رمز --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700">تکرار رمز جدید</label>
                <input type="password" name="password_confirmation"
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            {{-- دکمه --}}
            <div class="flex justify-end pt-4">
                <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-md transition-all bg-blue-600">
                    ذخیره تغییرات
                </button>
                <a href="{{ route('assets.index') }}" class="mr-2 bg-gray-300 text-black px-4 py-2 rounded">انصراف</a>
            </div>

        </form>
    </div>
</div>
@endsection
