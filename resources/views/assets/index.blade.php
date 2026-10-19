@extends('layouts.app')

@section('content')

@php
    $currentUser = Auth::user();
@endphp

<div class="page-header">
    <div>
        <h1>اموال من</h1>
        <p>{{ $assets->count() }} مال ثبت‌شده برای شما</p>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @if($currentUser->show_contract_button)
            <a href="{{ route('users.contract', $currentUser->id) }}" target="_blank" class="btn btn-ghost">مشاهده فرم اموال</a>
        @endif
        @if ($currentUser && $currentUser->allow_editing)
            <a href="{{ route('assets.create') }}" class="btn btn-primary">+ ثبت مال جدید</a>
        @endif
    </div>
</div>

<div class="card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="tbl">
            <thead>
                <tr>
                    <th>مال</th>
                    <th>تعداد</th>
                    <th>توضیحات</th>
                    <th>تاریخ بروزرسانی</th>
                    <th>تایید کاربر</th>
                    <th>تایید ادمین</th>
                    <th>کامنت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $asset->title }}</div>
                            <div class="muted">{{ $asset->asset_number }}</div>
                        </td>
                        <td>{{ $asset->count }}</td>
                        <td>{{ $asset->description ?: '—' }}</td>
                        <td>{{ \App\Support\Jalali::date($asset->updated_at_date) }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if ($asset->user_approved)
                                    <span class="badge badge-success">تایید شده</span>
                                @else
                                    <span class="badge badge-neutral">تایید نشده</span>
                                @endif
                                <form action="{{ route('assets.toggleUserApproval', $asset) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $asset->user_approved ? 'btn-danger-ghost' : 'btn-soft' }}">
                                        {{ $asset->user_approved ? 'لغو تایید' : 'تایید' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td>
                            @if ($asset->admin_approved)
                                <span class="badge badge-success">تایید شده</span>
                            @else
                                <span class="badge badge-neutral">تایید نشده</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('assets.edit',$asset) }}" class="btn btn-ghost btn-sm">
                                کامنت
                                @if ($asset->user_new_comment)<span class="badge badge-danger">جدید</span>@endif
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">هنوز مالی ثبت نشده است.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
