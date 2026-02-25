@extends('layouts.app')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">لیست کاربران</h2>
</div>

@if($newAlertsCount > 0)
    <div class="mb-4 text-center">
        <a href="{{ route('admin.alerts.index') }}"
           class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded shadow relative">
            🔔 هشدار جدید ({{ $newAlertsCount }})
        </a>
    </div>
@endif

{{-- پیام موفقیت --}}
@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded shadow">
        {{ session('success') }}
    </div>
@endif

<div class="overflow-x-auto shadow rounded-lg">
    <table class="min-w-full table-fixed bg-white" style="width: 100%;">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="w-1/3 text-center p-3 font-medium text-gray-700">نام</th>
                {{-- <th class="w-1/3 text-center p-3 font-medium text-gray-700">ایمیل</th> --}}
                <th class="w-1/3 text-center p-3 font-medium text-gray-700">IP آخرین ثبت</th>
                <th class="w-1/3 text-center p-3 font-medium text-gray-700">نام سیستم</th>
                <th class="w-1/3 text-center p-3 font-medium text-gray-700">عملیات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="border-b hover:bg-gray-50 transition duration-150">
                    <td class="p-3 text-center text-gray-800">{{ $user->name }}</td>
                    {{-- <td class="p-3 text-center text-gray-800">{{ $user->email }}</td> --}}
                    <td class="p-3 text-center text-gray-800">
                        {{ $user->latestLog->ip_address ?? '—' }}
                    </td>

                    <td class="p-3 text-center text-gray-800">
                        {{ $user->latestLog->hostname ?? '—' }}
                    </td>
                    <td class="p-3 text-center">
                        <a href= "{{ route('admin.users.showUserAssets', $user->id) }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded shadow transition duration-200">
                           مشاهده اموال
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="p-4 text-center text-gray-500">
                        هیچ کاربری ثبت نشده است.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
<div class="mt-4">
    {{-- {{ $users->links('vendor.pagination.tailwind') }} --}}
</div>

@endsection