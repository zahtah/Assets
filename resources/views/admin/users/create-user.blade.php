@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="bg-white rounded-lg shadow p-6">

        <h4 class="text-xl font-bold mb-6">ساخت کاربر</h4>

        <form method="POST" action="{{ route('store-user') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-gray-700 mb-1">نام</label>
                <input id="name" name="name" type="text"
                       placeholder="Enter User Name"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label for="email" class="block text-gray-700 mb-1">نام کاربری</label>
                <input id="email" name="email" type="text"
                       placeholder="Enter User Email"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label for="department" class="block text-gray-700 mb-1">ساختمان</label>
                <select id="department" name="department" type="text"
                       placeholder="Enter User Department"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                <option value="ستاد">ستاد</option>
                <option value="امور منابع آب مهدیشهر">امور منابع آب مهدیشهر</option>
                <option value="امور منابع آب سمنان">امور منابع آب سمنان</option>
                <option value="امور منابع آب شاهرود">امور منابع آب شاهرود</option>
                <option value="امور منابع آب دامغان">امور منابع آب دامغان</option>
                <option value="امور منابع آب گرمسار">امور منابع آب گرمسار</option>
                <option value="امور منابع آب میامی">امور منابع آب میامی</option>
            </select>
            </div>
            
            <div>
                <label for="unit" class="block text-gray-700 mb-1">واحد</label>
                <select id="unit" name="unit" type="text"
                       placeholder="Enter User Unit"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                <option value="برنامه ریزی">برنامه ریزی</option>
                <option value="حفاظت">حفاظت</option>
                <option value="IT">IT</option>
                <option value="مالی">مالی</option>
                <option value="مدیریت">مدیریت</option>
                <option value="مطالعات">مطالعات</option>
                <option value="طرح و توسعه">طرح و توسعه</option>
                <option value="انبار">انبار</option>
            </select>
            </div>

            <div>
                <label for="room_number" class="block text-gray-700 mb-1">شماره اتاق</label>
                <input id="room_number" name="room_number" type="number"
                       placeholder="Enter User Room Number"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>
            <div>
                <label for="floor" class="block text-gray-700 mb-1">طبقه</label>
                <select id="floor" name="floor"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    <option value="">-- انتخاب طبقه --</option>
                    @foreach(\App\Models\User::FLOORS as $floorOption)
                        <option value="{{ $floorOption }}" {{ old('floor') === $floorOption ? 'selected' : '' }}>{{ $floorOption }}</option>
                    @endforeach
                </select>
                @error('floor')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="block text-gray-700 mb-1">نوع دسترسی</label>
                <select id="role" name="role"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    @foreach(\App\Models\User::ASSIGNABLE_ROLES as $roleKey => $roleLabel)
                        <option value="{{ $roleKey }}" {{ old('role', 'user') === $roleKey ? 'selected' : '' }}>{{ $roleLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="password" class="block text-gray-700 mb-1">پسورد</label>
                <input id="password" name="password" type="password"
                       placeholder="Enter User Password"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label for="password_confirmation" class="block text-gray-700 mb-1"> تکرار پسورد</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       placeholder="Retype the Password"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <button type="submit"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition mt-2">
                ثبت
            </button>
            <a href="{{ route('all-users') }}" class="ml-2 bg-gray-300 text-black px-4 py-2 rounded">انصراف</a>

        </form>
    </div>
</div>
@endsection
