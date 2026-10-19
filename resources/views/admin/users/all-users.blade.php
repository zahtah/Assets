@extends('layouts.app')

@section('content')

@php
    $units = ['برنامه ریزی','حفاظت','IT','مالی','مدیریت','مطالعات','طرح و توسعه','اداره امور اداری','منابع انسانی و پشتیبانی','مدیریت منابع انسانی','انبار'];
    $hasFilter = request('search.name') || request('search.department') || request('search.unit');
@endphp

<div class="page-header">
    <div>
        <h1>کاربران و اموال</h1>
        <p>{{ $users->count() }} کاربر{{ $hasFilter ? ' (نتیجه‌ی جستجو)' : '' }}</p>
    </div>
    <a href="{{ route('create_user') }}" class="btn btn-primary">+ ساخت کاربر جدید</a>
</div>

@if($newAlertsCount > 0)
    <div class="banner">
        <span>{{ $newAlertsCount }} هشدار جدید (ثبت شماره اموال تکراری) در انتظار بررسی است.</span>
        <a href="{{ route('admin.alerts.index') }}">مشاهده هشدارها</a>
    </div>
@endif

<div class="card" style="overflow:hidden;">
    {{-- جستجو --}}
    <form id="searchForm" action="{{ route('all-users') }}" method="GET" class="toolbar">
        <input type="text" name="search[name]" value="{{ request('search.name', '') }}" placeholder="جستجوی نام کاربر..." style="width:200px;">
        <input type="text" name="search[department]" value="{{ request('search.department', '') }}" placeholder="ساختمان..." style="width:170px;">
        <select name="search[unit]" style="width:180px;">
            <option value="">همه‌ی واحدها</option>
            @foreach($units as $unitOption)
                <option value="{{ $unitOption }}" {{ request('search.unit') == $unitOption ? 'selected' : '' }}>{{ $unitOption }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">جستجو</button>
        @if($hasFilter)
            <a href="{{ route('all-users') }}" class="btn btn-ghost">پاک کردن فیلتر</a>
        @endif
    </form>

    <div style="overflow-x:auto;">
        <table class="tbl">
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>ساختمان / واحد</th>
                    <th>محل (اتاق / طبقه)</th>
                    <th>ثبت اموال توسط کاربر</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="cell-user">
                                <div class="cell-avatar">{{ mb_substr($user->name, 0, 1) }}</div>
                                <div>
                                    <div style="font-weight:600;">
                                        {{ $user->name }}
                                        @if ($user->new_comment)
                                            <span class="badge badge-danger">نظر جدید</span>
                                        @endif
                                        @if ($user->role === 'warehouse')
                                            <span class="badge badge-info">واحد انبار</span>
                                        @endif
                                    </div>
                                    <div class="muted">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $user->unit ?: '—' }}</div>
                            <div class="muted">{{ $user->department ?: '—' }}</div>
                        </td>
                        <td>
                            <div>{{ $user->room_number ? 'اتاق ' . $user->room_number : '—' }}</div>
                            <div class="muted">{{ $user->floor ?: '—' }}</div>
                        </td>
                        <td>
                            @if ($user->role === 'user')
                                <form action="{{ route('admin.users.toggleEditing', $user) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="switch {{ $user->allow_editing ? 'on' : '' }}" role="switch"
                                            aria-checked="{{ $user->allow_editing ? 'true' : 'false' }}"
                                            title="{{ $user->allow_editing ? 'غیرفعال‌سازی ثبت اموال' : 'فعال‌سازی ثبت اموال' }}">
                                        <span class="switch-track"></span>
                                        <span>{{ $user->allow_editing ? 'فعال' : 'غیرفعال' }}</span>
                                    </button>
                                </form>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                @if ($user->role === 'user')
                                    <a href="{{ route('admin.users.showUserAssets', $user->id) }}" class="btn btn-soft btn-sm">مشاهده اموال</a>
                                @endif
                                <a href="{{ route('edit-user', $user->id) }}" class="btn btn-ghost btn-sm">ویرایش</a>
                                <form action="{{ route('delete-user', $user->id) }}" method="POST"
                                      onsubmit="return confirm('آیا از حذف کاربر مطمئن هستید؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-ghost btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">کاربری پیدا نشد.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- دسترسی گروهی بر اساس واحد --}}
<details class="card" @if($errors->has('unit_name') || $errors->has('action')) open @endif>
    <summary>مدیریت گروهی دسترسی ثبت اموال بر اساس واحد</summary>
    <form action="{{ route('admin.users.bulk_allow_editing') }}" method="POST" class="card-body"
          style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;">
        @csrf
        <div>
            <label for="unit_name" style="display:block;font-size:.8rem;color:#64748b;margin-bottom:4px;">واحد</label>
            <select id="unit_name" name="unit_name" required style="width:240px;">
                <option value="">-- انتخاب واحد --</option>
                @foreach($units as $unitOption)
                    <option value="{{ $unitOption }}">{{ $unitOption }}</option>
                @endforeach
            </select>
            @error('unit_name') <div style="color:#b91c1c;font-size:.78rem;margin-top:4px;">{{ $message }}</div> @enderror
        </div>
        <div>
            <label for="action" style="display:block;font-size:.8rem;color:#64748b;margin-bottom:4px;">عملیات</label>
            <select name="action" id="action" required style="width:280px;">
                <option value="enable">فعال‌سازی ثبت اموال برای همه‌ی کاربران واحد</option>
                <option value="disable">غیرفعال‌سازی ثبت اموال برای همه‌ی کاربران واحد</option>
            </select>
            @error('action') <div style="color:#b91c1c;font-size:.78rem;margin-top:4px;">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary"
                onclick="return confirm('عملیات برای همه‌ی کاربران واحد انتخاب‌شده اعمال شود؟');">اجرای عملیات</button>
    </form>
</details>

@endsection
