@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="bg-white rounded-lg shadow p-6">

        <h4 class="text-xl font-bold mb-6">ویرایش کاربر</h4>

        <form method="POST" action="{{ route('update-user', $user->id) }}" class="space-y-4">
            @csrf
            @method('put')

            <div>
                <label for="name" class="block text-gray-700 mb-1">نام</label>
                <input id="name" name="name" type="text"
                       placeholder="Enter User Name"
                       value="{{ old('name', $user->name) }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label for="email" class="block text-gray-700 mb-1">نام کاربری</label>
                <input id="email" name="email" type="text"
                       placeholder="Enter User Email"
                       value="{{ old('email', $user->email) }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            {{-- <div>
                <label for="department" class="block text-gray-700 mb-1">معاونت</label>
                <input id="department" name="department" type="text"
                       placeholder="Enter User Department"
                       value="{{ old('department', $user->department) }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div> --}}
            <div>
                <label for="department" class="block text-gray-700 mb-1">ساختمان</label>
                <select id="department" name="department"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    @php
                        $current = old('department', $user->department);
                    @endphp
                    @foreach(['ستاد','امور منابع آب مهدیشهر','امور منابع آب سمنان','امور منابع آب شاهرود','امور منابع آب دامغان','امور منابع آب گرمسار','امور منابع آب میامی'] as $dep)
                        <option value="{{ $dep }}" {{ $current == $dep ? 'selected' : '' }}>{{ $dep }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="unit" class="block text-gray-700 mb-1">واحد</label>
                <select id="unit" name="unit"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">

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
                    <option value="انبار" {{ $current == 'انبار' ? 'selected' : '' }}>انبار</option>

                </select>
            </div>

            <div>
                <label for="room_number" class="block text-gray-700 mb-1">شماره اتاق</label>
                <input id="room_number" name="room_number" type="number"
                       placeholder="Enter User Room Number"
                       value="{{ old('room_number', $user->room_number) }}"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label for="floor" class="block text-gray-700 mb-1">طبقه</label>
                @php
                    $currentFloor = old('floor', $user->floor);
                @endphp
                <select id="floor" name="floor"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    <option value="">-- انتخاب طبقه --</option>
                    @foreach(\App\Models\User::FLOORS as $floorOption)
                        <option value="{{ $floorOption }}" {{ $currentFloor === $floorOption ? 'selected' : '' }}>{{ $floorOption }}</option>
                    @endforeach
                    {{-- اگر قبلاً مقدار دیگری برای طبقه ثبت شده، حفظ می‌شود تا اطلاعات قدیمی از بین نرود --}}
                    @if($currentFloor && !in_array($currentFloor, \App\Models\User::FLOORS, true))
                        <option value="{{ $currentFloor }}" selected>{{ $currentFloor }} (مقدار قبلی)</option>
                    @endif
                </select>
                @error('floor')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            @if($user->role !== 'admin')
            <div>
                <label for="role" class="block text-gray-700 mb-1">نوع دسترسی</label>
                @php
                    $currentRole = old('role', $user->role);
                @endphp
                <select id="role" name="role"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    @foreach(\App\Models\User::ASSIGNABLE_ROLES as $roleKey => $roleLabel)
                        <option value="{{ $roleKey }}" {{ $currentRole === $roleKey ? 'selected' : '' }}>{{ $roleLabel }}</option>
                    @endforeach
                </select>
            </div>
            @endif


            <div>
                <label for="password" class="block text-gray-700 mb-1">پسورد</label>
                <input id="password" name="password" type="password"
                       placeholder="Enter User Password"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            <div>
                <label for="password_confirmation" class="block text-gray-700 mb-1">تکرار پسورد</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       placeholder="Retype the Password"
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
            </div>

            {{-- @if (!$user->hasVerifiedEmail())
                <div class="flex items-center">
                    <input type="checkbox" id="verify" name="verify" class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="verify" class="text-gray-700">Verify User</label>
                </div>
            @endif --}}

            <button type="submit"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition mt-2">
                ثبت
            </button>
            <a href="{{ route('all-users') }}" class="ml-2 bg-gray-300 text-black px-4 py-2 rounded">انصراف</a>

        </form>
    </div>
</div>
@endsection
