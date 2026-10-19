@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>لاگ ورود کاربران</h1>
        <p>آخرین ورود هر کاربر به سیستم (تاریخ شمسی، به وقت تهران)</p>
    </div>
</div>

<div class="card" style="overflow:hidden;">
    <form action="{{ route('admin.users') }}" method="GET" class="toolbar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="جستجو بر اساس نام..." style="width:240px;">
        <button type="submit" class="btn btn-primary">جستجو</button>
        @if(request('search') !== null && request('search') !== '')
            <a href="{{ route('admin.users') }}" class="btn btn-ghost">پاک کردن فیلتر</a>
        @endif
    </form>

    <div style="overflow-x:auto;">
        <table class="tbl">
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>IP آخرین ورود</th>
                    <th>نام سیستم</th>
                    <th>تاریخ و ساعت آخرین ورود</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="cell-user">
                                <div class="cell-avatar">{{ mb_substr($user->name, 0, 1) }}</div>
                                <div>
                                    <div style="font-weight:600;">
                                        {{ $user->name }}
                                        @if ($user->new_comment)<span class="badge badge-danger">نظر جدید</span>@endif
                                    </div>
                                    <div class="muted">{{ $user->unit ?: $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->latestLogin->ip_address ?? '—' }}</td>
                        <td>{{ $user->latestLogin->hostname ?? '—' }}</td>
                        <td>
                            @if($user->last_login_at)
                                {{ \App\Support\Jalali::dateTime($user->last_login_at) }}
                            @else
                                <span class="badge badge-neutral">هنوز ورودی ثبت نشده</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state">هیچ کاربری ثبت نشده است.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('partials.simple-pagination', ['paginator' => $users])

@endsection
