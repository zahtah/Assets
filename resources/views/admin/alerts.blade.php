@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>هشدارهای ثبت کد تکراری</h1>
        <p>مواردی که کاربری شماره اموالی را ثبت کرده که قبلاً برای کاربر دیگری ثبت شده بود</p>
    </div>
    <div style="display:flex;gap:8px;">
        <form method="POST" action="{{ route('admin.alerts.markRead') }}">
            @csrf
            <button type="submit" class="btn btn-primary">علامت‌گذاری همه به عنوان خوانده‌شده</button>
        </form>
        <a href="{{ route('all-users') }}" class="btn btn-ghost">بازگشت به کاربران</a>
    </div>
</div>

<div class="card" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="tbl">
            <thead>
                <tr>
                    <th>کد تکراری</th>
                    <th>ثبت‌کننده‌ی جدید</th>
                    <th>ثبت‌کننده‌ی قبلی</th>
                    <th>تاریخ ثبت قبلی</th>
                    <th>وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                    <tr>
                        <td style="font-weight:700;">{{ $alert->asset_number }}</td>
                        <td>{{ $alert->newUser->name ?? '—' }}</td>
                        <td>{{ $alert->originalUser->name ?? '—' }}</td>
                        <td>{{ $alert->original_created_at ? \App\Support\Jalali::dateTime($alert->original_created_at) : '—' }}</td>
                        <td>
                            @if(!$alert->is_read)
                                <span class="badge badge-danger">جدید</span>
                            @else
                                <span class="badge badge-neutral">خوانده‌شده</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="empty-state">هشداری وجود ندارد.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
