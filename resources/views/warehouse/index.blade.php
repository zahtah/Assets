@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>اموال انبار</h1>
        <p>خارج کردن مال از مالکیت کاربر و اختصاص اموال بدون مالک</p>
    </div>
    <a href="{{ route('warehouse.assets.create') }}" class="btn btn-primary">+ ثبت مال جدید در انبار</a>
</div>

@if($errors->any())
    <div class="flash flash-error" style="display:block;">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

{{-- ۱) جستجو و خارج کردن از مالکیت --}}
<div class="card" style="overflow:hidden;">
    <div class="card-head">۱) خارج کردن مال از مالکیت کاربر</div>
    <form action="{{ route('warehouse.index') }}" method="GET" class="toolbar">
        <input type="text" name="q" value="{{ $q }}" placeholder="جستجو با شماره اموال..." style="width:240px;" autofocus>
        <button type="submit" class="btn btn-primary">جستجو</button>
        @if($q !== '')
            <a href="{{ route('warehouse.index') }}" class="btn btn-ghost">پاک کردن</a>
        @endif
        <span class="muted">با خارج کردن مال از مالکیت، مال بدون کاربر در انبار قرار می‌گیرد.</span>
    </form>

    @if($q !== '')
        <div style="overflow-x:auto;">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>مال</th>
                        <th>تعداد</th>
                        <th>توضیحات</th>
                        <th>مالک فعلی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($found as $asset)
                        <tr>
                            <td>
                                <div style="font-weight:600;">{{ $asset->title }}</div>
                                <div class="muted">{{ $asset->asset_number }}</div>
                            </td>
                            <td>{{ $asset->count }}</td>
                            <td>{{ $asset->description ?: '—' }}</td>
                            <td>
                                @if($asset->user)
                                    {{ $asset->user->name }}
                                    <div class="muted">
                                        {{ $asset->user->unit }}
                                        @if($asset->user->room_number) · اتاق {{ $asset->user->room_number }} @endif
                                        @if($asset->user->floor) · {{ $asset->user->floor }} @endif
                                    </div>
                                @else
                                    <span class="badge badge-success">در انبار (بدون مالک)</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->user)
                                    <form action="{{ route('warehouse.release', $asset) }}" method="POST"
                                          onsubmit="return confirm(@js('مال شماره ' . $asset->asset_number . ' از مالکیت «' . $asset->user->name . '» خارج و به انبار منتقل شود؟'));">
                                        @csrf
                                        <input type="hidden" name="q" value="{{ $q }}">
                                        <button type="submit" class="btn btn-danger-ghost btn-sm">خارج کردن از مالکیت</button>
                                    </form>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state">مالی با این شماره پیدا نشد.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ۲) اموال بدون مالک --}}
<div class="card" style="overflow:hidden;">
    <div class="card-head">۲) اموال بدون مالک <span class="badge badge-neutral" style="margin-right:6px;">{{ $unowned->total() }}</span></div>

    <form action="{{ route('warehouse.index') }}" method="GET" class="toolbar">
        @if($q !== '')<input type="hidden" name="q" value="{{ $q }}">@endif
        <input type="text" name="title" value="{{ $title }}" placeholder="عنوان (مثلاً میز)" style="width:200px;">
        <input type="text" name="number" value="{{ $number }}" placeholder="شماره اموال" style="width:180px;">
        <button type="submit" class="btn btn-primary">فیلتر</button>
        @if($title !== '' || $number !== '')
            <a href="{{ route('warehouse.index', array_filter(['q' => $q])) }}" class="btn btn-ghost">نمایش همه</a>
        @endif
    </form>

    <form action="{{ route('warehouse.assign') }}" method="POST">
        @csrf

        <div style="overflow-x:auto;">
            <table class="tbl">
                <thead>
                    <tr>
                        <th style="width:44px;"><input type="checkbox" id="check-all" title="انتخاب همه‌ی این صفحه"></th>
                        <th>مال</th>
                        <th>تعداد</th>
                        <th>توضیحات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unowned as $asset)
                        <tr>
                            <td>
                                <input type="checkbox" name="asset_ids[]" value="{{ $asset->id }}" class="asset-check"
                                       @checked(is_array(old('asset_ids')) && in_array($asset->id, old('asset_ids')))>
                            </td>
                            <td>
                                <div style="font-weight:600;">{{ $asset->title }}</div>
                                <div class="muted">{{ $asset->asset_number }}</div>
                            </td>
                            <td>{{ $asset->count }}</td>
                            <td>{{ $asset->description ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state">مالِ بدون مالکی وجود ندارد.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($unowned->count() > 0)
            <div class="toolbar" style="border-top:1px solid #e2e8f0;border-bottom:0;background:#f8fafc;">
                <span style="font-weight:600;font-size:.88rem;">اختصاص موارد انتخاب‌شده به:</span>
                <input type="text" id="user-filter" placeholder="جستجوی کاربر..." style="width:170px;">
                <select name="user_id" id="user-select" style="width:280px;">
                    <option value="">-- انتخاب کاربر --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>
                            {{ $u->name }}@if($u->unit) - {{ $u->unit }}@endif @if($u->room_number) - اتاق {{ $u->room_number }}@endif
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('مال‌های انتخاب‌شده به کاربر انتخاب‌شده اختصاص داده شود؟');">اختصاص به کاربر</button>
            </div>
        @endif
    </form>
</div>

@include('partials.simple-pagination', ['paginator' => $unowned])

<script>
    const checkAll = document.getElementById('check-all');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.asset-check').forEach(function (c) { c.checked = checkAll.checked; });
        });
    }
    const userFilter = document.getElementById('user-filter');
    const userSelect = document.getElementById('user-select');
    if (userFilter && userSelect) {
        userFilter.addEventListener('input', function () {
            const term = userFilter.value.trim();
            Array.from(userSelect.options).forEach(function (opt, i) {
                if (i === 0) return;
                const match = term === '' || opt.text.indexOf(term) !== -1;
                opt.hidden = !match;
                opt.disabled = !match;
            });
        });
    }
</script>

@endsection
