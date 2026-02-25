@extends('layouts.app')

@section('content')

<div class="max-w-6xl mx-auto bg-white shadow rounded-lg p-6">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-red-600">
            هشدارهای ثبت کد تکراری
        </h2>

        <form method="POST" action="{{ route('admin.alerts.markRead') }}">
            @csrf
            <button type="submit"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow">
                علامت‌گذاری همه به عنوان خوانده شده
            </button>
        </form>
        {{-- دکمه بازگشت --}}
            <a href="{{ route('admin.users') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow transition duration-200">
                بازگشت به لیست کاربران
            </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-center border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">کد تکراری</th>
                    <th>کاربر ثبت‌کننده جدید</th>
                    <th>کاربر ثبت‌کننده قبلی</th>
                    <th>تاریخ ثبت قبلی</th>
                    <th>وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alerts as $alert)
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 text-red-600 font-bold">
                        {{ $alert->asset_number }}
                    </td>
                    <td>{{ $alert->newUser->name }}</td>
                    <td>{{ $alert->originalUser->name }}</td>
                    <td>
                        {{ $alert->original_created_at }}
                    </td>
                    <td>
                        @if(!$alert->is_read)
                            <span class="text-red-600 font-bold">جدید</span>
                        @else
                            <span class="text-green-600">خوانده شده</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection