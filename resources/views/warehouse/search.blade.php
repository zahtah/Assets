@extends('layouts.app')

@section('content')

@php
    $units = ['برنامه ریزی','حفاظت','IT','مالی','مدیریت','مطالعات','طرح و توسعه','اداره امور اداری','منابع انسانی و پشتیبانی','مدیریت منابع انسانی','انبار'];
    $isAdmin = auth()->user()->role === 'admin';
    $hasFilter = $searched;
@endphp

<div class="page-header">
    <div>
        <h1>جستجوی اموال</h1>
        <p>با شماره اموال یا ویژگی‌های دیگر جستجو کنید تا ببینید مال وجود دارد یا نه و در اختیار چه کسی است</p>
    </div>
</div>

<div class="card" style="overflow:hidden;">
    <form action="{{ route('warehouse.search') }}" method="GET" class="toolbar">
        <input type="text" name="q" value="{{ $q }}" placeholder="شماره اموال، عنوان یا توضیحات (مثلاً: میز 1204)" style="flex:1 1 320px;min-width:240px;" autofocus>
        <input type="text" name="owner" value="{{ $owner }}" placeholder="نام مالک" style="width:160px;">
        <select name="unit" style="width:170px;">
            <option value="">همه‌ی واحدها</option>
            @foreach($units as $u)
                <option value="{{ $u }}" @selected($unit === $u)>{{ $u }}</option>
            @endforeach
        </select>
        <select name="status" style="width:150px;">
            <option value="">همه‌ی وضعیت‌ها</option>
            <option value="owned" @selected($status === 'owned')>دارای مالک</option>
            <option value="unowned" @selected($status === 'unowned')>در انبار (بدون مالک)</option>
        </select>
        <button type="submit" class="btn btn-primary">جستجو</button>
        @if($hasFilter)
            <a href="{{ route('warehouse.search') }}" class="btn btn-ghost">پاک کردن</a>
        @endif
    </form>

    @if(!$searched)
        <div class="empty-state">برای شروع، شماره اموال یا بخشی از مشخصات مال را وارد کنید.</div>
    @else
        <div style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
            @if($assets->total() > 0)
                <span class="badge badge-success">{{ $assets->total() }} مال پیدا شد</span>
            @else
                <span class="badge badge-danger">مالی با این مشخصات پیدا نشد</span>
            @endif
        </div>

        @if($assets->total() > 0)
            <div style="overflow-x:auto;">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>مال</th>
                            <th>تعداد</th>
                            <th>توضیحات</th>
                            <th>در اختیار</th>
                            <th>آخرین بروزرسانی</th>
                            <th>مشاهده</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assets as $asset)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $asset->title }}</div>
                                    <div class="muted">{{ $asset->asset_number }}</div>
                                </td>
                                <td>{{ $asset->count }}</td>
                                <td>{{ $asset->description ?: '—' }}</td>
                                <td>
                                    @if($asset->user)
                                        <div style="font-weight:600;">{{ $asset->user->name }}</div>
                                        <div class="muted">
                                            {{ $asset->user->unit ?: '—' }}
                                            @if($asset->user->department) · {{ $asset->user->department }} @endif
                                            @if($asset->user->room_number) · اتاق {{ $asset->user->room_number }} @endif
                                            @if($asset->user->floor) · {{ $asset->user->floor }} @endif
                                        </div>
                                    @else
                                        <span class="badge badge-success">در انبار (بدون مالک)</span>
                                    @endif
                                </td>
                                <td>{{ \App\Support\Jalali::date($asset->updated_at_date) }}</td>
                                <td>
                                    <div class="actions">
                                        @if($isAdmin && $asset->user_id)
                                            <a href="{{ route('admin.users.showUserAssets', [$asset->user_id, 'highlight' => $asset->id]) }}#asset-{{ $asset->id }}" class="btn btn-soft btn-sm">اموال این کاربر</a>
                                        @endif
                                        <a href="{{ route('warehouse.index', ['q' => $asset->asset_number]) }}" class="btn btn-ghost btn-sm">مدیریت در انبار</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>

@if($assets)
    @include('partials.simple-pagination', ['paginator' => $assets])
@endif

@endsection
