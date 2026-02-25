@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">اموال من</h2>
    <a href="{{ route('assets.create') }}"
       class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded shadow transition duration-200">
        ثبت مال جدید
    </a>
</div>

{{-- پیام موفقیت --}}
{{-- @if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded shadow">
        {{ session('success') }}
    </div>
@endif --}}

<div class="overflow-x-auto">
    <table class="min-w-full bg-white shadow rounded-lg table-fixed" >
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="w-1/4 text-center p-3 font-medium text-gray-700">عنوان</th>
                <th class="w-1/6 text-center p-3 font-medium text-gray-700">شماره</th>
                <th class="w-1/6 text-center p-3 font-medium text-gray-700">ستاد</th>
                <th class="w-1/6 text-center p-3 font-medium text-gray-700">تاریخ</th>
                {{-- <th class="w-1/6 text-center p-3 font-medium text-gray-700">نام هاست</th> --}}
                <th class="w-1/6 text-center p-3 font-medium text-gray-700">عملیات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
                <tr class="border-b hover:bg-gray-50 transition duration-150">
                    <td class="p-3 text-gray-800 text-center">{{ $asset->title }}</td>
                    <td class="p-3 text-gray-800 text-center">{{ $asset->asset_number }}</td>
                    <td class="p-3 text-gray-800 text-center">{{ $asset->city }}</td>
                    <td class="p-3 text-gray-800 text-center">
                        {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($asset->updated_at_date))->format('Y/m/d') }}
                    </td>
                    {{-- <td class="p-3 text-gray-800 text-center">{{ $log->hostname }}</td> --}}
                    <td class="p-3 text-center">
                        <a href="{{ route('assets.edit',$asset) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium mr-2">ویرایش</a>
                        <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('آیا مطمئن هستید که می‌خواهید این مال را حذف کنید؟')"
                                    class="text-red-600 hover:text-red-800 font-medium">
                                حذف
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-gray-500">
                        هنوز مال ثبت نشده است.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
{{-- <div class="mt-4">
    {{ $assets->links('vendor.pagination.tailwind') }}
</div> --}}

@endsection