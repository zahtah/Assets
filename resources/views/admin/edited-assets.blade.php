@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>اموال ویرایش‌شده</h1>
        <p>{{ $assets->total() }} مال در این نتیجه — برای دیدن تغییرات و کامنت‌ها روی ردیف کلیک کنید</p>
    </div>
    <a href="{{ route('all-users') }}" class="btn btn-ghost">بازگشت به کاربران</a>
</div>

@if($invalidDates)
    <div class="flash flash-error">تاریخ واردشده نامعتبر است؛ فرمت صحیح مانند 1405/07/21 است (فیلتر تاریخ نامعتبر نادیده گرفته شد).</div>
@endif

@php $hasFilter = collect(request()->only(['from','to','user','number','action']))->filter()->isNotEmpty(); @endphp

<div class="card" style="overflow:hidden;">
    <form action="{{ route('admin.edited-assets') }}" method="GET" class="toolbar">
        <input type="text" name="from" value="{{ request('from') }}" placeholder="از تاریخ (1405/07/01)" style="width:160px;">
        <input type="text" name="to" value="{{ request('to') }}" placeholder="تا تاریخ (1405/07/30)" style="width:160px;">
        <input type="text" name="user" value="{{ request('user') }}" placeholder="نام کاربر" style="width:150px;">
        <input type="text" name="number" value="{{ request('number') }}" placeholder="شماره اموال" style="width:140px;">
        <select name="action" style="width:230px;">
            <option value="">همه‌ی انواع تغییر</option>
            @foreach($actionLabels as $key => $label)
                <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">اعمال فیلتر</button>
        @if($hasFilter)
            <a href="{{ route('admin.edited-assets') }}" class="btn btn-ghost">پاک کردن فیلتر</a>
        @endif
    </form>

    <div style="overflow-x:auto;">
        <table class="tbl">
            <thead>
                <tr>
                    <th>مال</th>
                    <th>مالک فعلی</th>
                    <th>آخرین تغییر</th>
                    <th>نوع تغییر</th>
                    <th>انجام‌دهنده</th>
                    <th>تعداد تغییرات</th>
                    <th>کامنت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    @php
                        $last = $lastEdits[$asset->id] ?? null;
                        $url = $asset->user_id
                            ? route('admin.users.showUserAssets', [$asset->user_id, 'highlight' => $asset->id]) . '#asset-' . $asset->id
                            : route('warehouse.index', ['q' => $asset->asset_number]);
                    @endphp
                    <tr style="cursor:pointer;" onclick="window.location='{{ $url }}'">
                        <td>
                            <div style="font-weight:600;">{{ $asset->title }}</div>
                            <div class="muted">{{ $asset->asset_number }}</div>
                        </td>
                        <td>
                            @if($asset->user)
                                <a href="{{ $url }}" style="color:#1d4ed8;font-weight:600;">{{ $asset->user->name }}</a>
                                <div class="muted">{{ $asset->user->unit }}</div>
                            @else
                                <span class="badge badge-success">در انبار</span>
                            @endif
                        </td>
                        <td>{{ \App\Support\Jalali::dateTime($asset->last_edit_at) }}</td>
                        <td>
                            {{ $last?->action_label ?? '—' }}
                            @if($last && $last->action === 'release' && $last->previousOwner)
                                <div class="muted">از: {{ $last->previousOwner->name }}</div>
                            @endif
                        </td>
                        <td>{{ $last?->editor?->name ?? '—' }}</td>
                        <td>{{ $asset->edits_in_range }}</td>
                        <td>
                            @if($asset->admin_new_comment)<span class="badge badge-danger">نظر جدید</span>@endif
                            @if($asset->comments_count > 0)
                                <span class="muted">{{ $asset->comments_count }} کامنت</span>
                            @elseif(!$asset->admin_new_comment)
                                <span class="muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">مالِ ویرایش‌شده‌ای در این بازه پیدا نشد.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.simple-pagination', ['paginator' => $assets])

@endsection
