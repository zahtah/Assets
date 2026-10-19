<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $pageTitle ?? 'سیستم اموال شرکت' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- استایل چیدمان (سایدبار) به‌صورت مستقل از build تیلویند؛ با تغییر متغیرهای :root می‌توان رنگ‌ها را عوض کرد --}}
    <style>
        :root {
            --sb-bg: #0f172a;
            --sb-bg-hover: #1e293b;
            --sb-text: #cbd5e1;
            --sb-muted: #64748b;
            --accent: #2563eb;
            --page-bg: #f1f5f9;
            --sb-width: 264px;
        }
        body { background: var(--page-bg); margin: 0; }

        /* ---------- سایدبار ---------- */
        .app-sidebar {
            position: fixed; top: 0; right: 0; bottom: 0; width: var(--sb-width);
            background: var(--sb-bg); color: var(--sb-text);
            display: flex; flex-direction: column; z-index: 50;
            transition: transform .25s ease;
        }
        .sb-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 18px 18px; border-bottom: 1px solid rgba(255,255,255,.08);
            color: #fff; font-weight: 700; font-size: 1rem; text-decoration: none;
        }
        .sb-brand-logo {
            width: 36px; height: 36px; border-radius: 10px; background: var(--accent);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .sb-brand small { display: block; font-weight: 400; font-size: .72rem; color: var(--sb-muted); margin-top: 2px; }
        .sb-nav { flex: 1; overflow-y: auto; padding: 14px 12px; }
        .sb-section {
            font-size: .7rem; letter-spacing: .02em; color: var(--sb-muted);
            padding: 14px 10px 6px; font-weight: 600;
        }
        .sb-link {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; margin-bottom: 2px; border-radius: 10px;
            color: var(--sb-text); text-decoration: none; font-size: .92rem;
            background: transparent; border: 0; width: 100%; cursor: pointer; text-align: right;
            font-family: inherit; transition: background .15s, color .15s;
        }
        .sb-link svg { width: 20px; height: 20px; flex-shrink: 0; opacity: .85; }
        .sb-link:hover { background: var(--sb-bg-hover); color: #fff; }
        .sb-link.active { background: var(--accent); color: #fff; font-weight: 600; box-shadow: 0 4px 12px rgba(37,99,235,.35); }
        .sb-link.active svg { opacity: 1; }
        .sb-badge {
            margin-right: auto; background: #ef4444; color: #fff; border-radius: 999px;
            font-size: .7rem; font-weight: 700; min-width: 20px; height: 20px; padding: 0 6px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .sb-footer { border-top: 1px solid rgba(255,255,255,.08); padding: 12px; }
        .sb-user { display: flex; align-items: center; gap: 10px; padding: 6px 8px 12px; }
        .sb-avatar {
            width: 38px; height: 38px; border-radius: 50%; background: #334155; color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;
        }
        .sb-user-name { color: #fff; font-size: .9rem; font-weight: 600; line-height: 1.3; }
        .sb-user-role { color: var(--sb-muted); font-size: .72rem; }
        .sb-logout:hover { background: rgba(239,68,68,.15); color: #fca5a5; }

        /* ---------- بدنه ---------- */
        .app-content { margin-right: var(--sb-width); min-height: 100vh; }
        .app-topbar {
            display: none; align-items: center; gap: 12px; padding: 10px 16px;
            background: #fff; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 40;
        }
        .app-topbar button { background: none; border: 0; cursor: pointer; padding: 4px; color: #0f172a; }
        .app-main { padding: 28px 32px 48px; max-width: 1400px; margin: 0 auto; }
        .app-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 45; }

        /* پیام‌ها */
        .flash { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; font-size: .92rem; border: 1px solid; }
        .flash-success { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }
        .flash-error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }

        /* ظاهر یکدست محتوای صفحات (با :where تا کلاس‌های تیلویند همچنان اولویت داشته باشند) */
        .app-main :where(h2) { font-size: 1.35rem; font-weight: 700; color: #0f172a; }
        .app-main :where(table) { border-collapse: collapse; }
        .app-main :where(thead th) { background: #f8fafc; color: #475569; font-size: .82rem; font-weight: 600; white-space: nowrap; }
        .app-main :where(tbody td) { font-size: .9rem; }
        .app-main :where(input[type=text], input[type=number], input[type=password], input[type=email], input[type=search], select, textarea) {
            border: 1px solid #cbd5e1; border-radius: 8px; padding: 7px 11px; background: #fff; font-family: inherit;
        }
        .app-main :where(input:focus, select:focus, textarea:focus) { outline: 2px solid #bfdbfe; border-color: var(--accent); }
        .app-main :where(.shadow) { box-shadow: 0 1px 3px rgba(15,23,42,.08), 0 1px 2px rgba(15,23,42,.04); }

        /* ---------- کامپوننت‌های مشترک (برای صفحات جدید/بازطراحی‌شده) ---------- */
        .page-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
        .page-header h1 { font-size: 1.4rem; font-weight: 700; color: #0f172a; margin: 0; }
        .page-header p { color: #64748b; font-size: .85rem; margin: 4px 0 0; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 2px rgba(15,23,42,.04); margin-bottom: 20px; }
        .card-body { padding: 18px 20px; }
        .card-head { padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #0f172a; font-size: .95rem; }
        .toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 14px 20px; border-bottom: 1px solid #e2e8f0; }
        .toolbar .grow { flex: 1 1 auto; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 14px; border-radius: 9px; font-size: .86rem; font-weight: 600; font-family: inherit;
            border: 1px solid transparent; cursor: pointer; text-decoration: none; line-height: 1.2; white-space: nowrap;
            transition: background .15s, border-color .15s, color .15s;
        }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-ghost { background: #fff; color: #334155; border-color: #cbd5e1; }
        .btn-ghost:hover { background: #f1f5f9; }
        .btn-soft { background: #eff6ff; color: #1d4ed8; }
        .btn-soft:hover { background: #dbeafe; }
        .btn-danger-ghost { background: #fff; color: #b91c1c; border-color: #fecaca; }
        .btn-danger-ghost:hover { background: #fef2f2; }
        .btn-sm { padding: 5px 10px; font-size: .8rem; border-radius: 8px; }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 9px; border-radius: 999px; font-size: .72rem; font-weight: 600; }
        .badge-neutral { background: #f1f5f9; color: #475569; }
        .badge-info { background: #eff6ff; color: #1d4ed8; }
        .badge-warn { background: #fffbeb; color: #b45309; }
        .badge-danger { background: #fef2f2; color: #b91c1c; }
        .badge-success { background: #ecfdf5; color: #047857; }
        .tbl { width: 100%; border-collapse: collapse; }
        .tbl th { text-align: right; padding: 11px 16px; background: #f8fafc; color: #64748b; font-size: .78rem; font-weight: 600; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
        .tbl td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; font-size: .88rem; color: #1e293b; vertical-align: middle; }
        .tbl tbody tr:last-child td { border-bottom: 0; }
        .tbl tbody tr:hover td { background: #f8fafc; }
        .tbl .muted { color: #94a3b8; font-size: .76rem; }
        .tbl .actions { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
        .cell-user { display: flex; align-items: center; gap: 11px; }
        .cell-avatar { width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #3730a3; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; }
        .empty-state { padding: 40px 16px; text-align: center; color: #94a3b8; }
        /* کلید روشن/خاموش */
        .switch { display: inline-flex; align-items: center; gap: 8px; background: none; border: 0; cursor: pointer; font-family: inherit; font-size: .8rem; color: #475569; padding: 0; }
        .switch-track { width: 38px; height: 22px; border-radius: 999px; background: #cbd5e1; position: relative; transition: background .15s; flex-shrink: 0; }
        .switch-track::after { content: ''; position: absolute; top: 3px; right: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.25); transition: right .15s; }
        .switch.on .switch-track { background: #16a34a; }
        .switch.on .switch-track::after { right: 19px; }
        .banner { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; border-radius: 12px; margin-bottom: 18px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: .9rem; }
        .banner a { color: #92400e; font-weight: 700; text-decoration: underline; }
        details.card > summary { list-style: none; cursor: pointer; padding: 14px 20px; font-weight: 700; color: #0f172a; font-size: .95rem; display: flex; align-items: center; justify-content: space-between; }
        details.card > summary::-webkit-details-marker { display: none; }
        details.card > summary::after { content: '﹀'; color: #94a3b8; font-size: .8rem; }
        details.card[open] > summary { border-bottom: 1px solid #e2e8f0; }
        details.card[open] > summary::after { content: '︿'; }

        /* ---------- موبایل / تبلت ---------- */
        @media (max-width: 1023px) {
            .app-sidebar { transform: translateX(100%); }
            body.sb-open .app-sidebar { transform: translateX(0); box-shadow: -8px 0 30px rgba(0,0,0,.3); }
            body.sb-open .app-overlay { display: block; }
            .app-content { margin-right: 0; }
            .app-topbar { display: flex; }
            .app-main { padding: 18px 14px 40px; }
        }
    </style>
</head>
<body>

@php
    $authUser = auth()->user();
    $role = $authUser->role;
    $roleLabel = ['admin' => 'مدیر سیستم', 'warehouse' => 'واحد انبار', 'user' => 'کاربر'][$role] ?? $role;

    $unreadAlerts = $role === 'admin'
        ? \App\Models\AssetDuplicateAlert::where('is_read', false)->count()
        : 0;

    // آیکون‌ها (Heroicons - outline)
    $icons = [
        'box'      => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
        'user'     => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'users'    => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'pencil'   => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10',
        'warehouse'=> 'M2.25 21h19.5M3.75 21V9.349m16.5 11.651V9.349M3.75 9.349a3 3 0 001.5-5.599h13.5a3 3 0 001.5 5.599M3.75 9.349A2.99 2.99 0 006 10.5c.8 0 1.52-.31 2.06-.82a2.99 2.99 0 004.12 0 2.99 2.99 0 004.12 0c.54.51 1.26.82 2.06.82.9 0 1.7-.38 2.25-1.151M9 21v-6a1.5 1.5 0 011.5-1.5h3A1.5 1.5 0 0115 15v6',
        'plus'     => 'M12 4.5v15m7.5-7.5h-15',
        'bell'     => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
        'clock'    => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'download' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3',
        'upload'   => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
        'logout'   => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75',
        'search'   => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        'menu'     => 'M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5',
    ];
    $ico = fn ($k) => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="' . $icons[$k] . '"/></svg>';
@endphp

<div class="app-overlay" onclick="document.body.classList.remove('sb-open')"></div>

<aside class="app-sidebar">
    <a class="sb-brand" href="{{ route($authUser->homeRoute()) }}">
        <span class="sb-brand-logo">{!! str_replace('<svg', '<svg style="width:20px;height:20px;color:#fff"', $ico('box')) !!}</span>
        <span>سیستم اموال<small>مدیریت و پیگیری اموال شرکت</small></span>
    </a>

    <nav class="sb-nav">
        @if($role === 'admin')
            <div class="sb-section">مدیریت</div>
            <a href="{{ route('all-users') }}" class="sb-link {{ request()->routeIs('all-users','create_user','edit-user','admin.users.showUserAssets','admin.users.assets.*','admin.users.toggleEditing','admin.users.bulk_*') ? 'active' : '' }}">
                {!! $ico('users') !!} <span>کاربران و اموال</span>
            </a>
            <a href="{{ route('admin.edited-assets') }}" class="sb-link {{ request()->routeIs('admin.edited-assets') ? 'active' : '' }}">
                {!! $ico('pencil') !!} <span>اموال ویرایش‌شده</span>
            </a>
            <a href="{{ route('admin.alerts.index') }}" class="sb-link {{ request()->routeIs('admin.alerts.*') ? 'active' : '' }}">
                {!! $ico('bell') !!} <span>هشدارها</span>
                @if($unreadAlerts > 0)<span class="sb-badge">{{ $unreadAlerts }}</span>@endif
            </a>
            <a href="{{ route('admin.users') }}" class="sb-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                {!! $ico('clock') !!} <span>لاگ ورود کاربران</span>
            </a>
        @endif

        @if(in_array($role, ['admin', 'warehouse'], true))
            <div class="sb-section">انبار</div>
            <a href="{{ route('warehouse.search') }}" class="sb-link {{ request()->routeIs('warehouse.search') ? 'active' : '' }}">
                {!! $ico('search') !!} <span>جستجوی اموال</span>
            </a>
            <a href="{{ route('warehouse.index') }}" class="sb-link {{ request()->routeIs('warehouse.index','warehouse.release','warehouse.assign') ? 'active' : '' }}">
                {!! $ico('warehouse') !!} <span>اموال انبار</span>
            </a>
            <a href="{{ route('warehouse.assets.create') }}" class="sb-link {{ request()->routeIs('warehouse.assets.*') ? 'active' : '' }}">
                {!! $ico('plus') !!} <span>ثبت مال جدید در انبار</span>
            </a>
        @endif

        @if($role === 'user')
            <div class="sb-section">اموال</div>
            <a href="{{ route('assets.index') }}" class="sb-link {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                {!! $ico('box') !!} <span>اموال من</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="sb-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                {!! $ico('user') !!} <span>پروفایل من</span>
            </a>
        @endif

        @if($role === 'admin')
            <div class="sb-section">ورود و خروج اطلاعات</div>
            <a href="{{ route('admin.export.assets') }}" class="sb-link">
                {!! $ico('download') !!} <span>خروجی اکسل اموال</span>
            </a>
            <form id="import-form" action="{{ route('import.users.assets') }}" method="POST" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input type="file" id="import-file" name="excel_file" accept=".xlsx,.xls,.csv" required
                       onchange="if(this.files.length && confirm('فایل «' + this.files[0].name + '» بارگذاری شود؟')){ this.form.submit(); } else { this.value=''; }">
            </form>
            <button type="button" class="sb-link" onclick="document.getElementById('import-file').click()">
                {!! $ico('upload') !!} <span>آپلود فایل اکسل</span>
            </button>
        @endif
    </nav>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ mb_substr($authUser->name, 0, 1) }}</div>
            <div>
                <div class="sb-user-name">{{ $authUser->name }}</div>
                <div class="sb-user-role">{{ $roleLabel }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-link sb-logout">{!! $ico('logout') !!} <span>خروج از سیستم</span></button>
        </form>
    </div>
</aside>

<div class="app-content">
    <div class="app-topbar">
        <button type="button" aria-label="منو" onclick="document.body.classList.add('sb-open')">
            <span style="display:block;width:26px;height:26px;">{!! $ico('menu') !!}</span>
        </button>
        <strong>سیستم اموال شرکت</strong>
    </div>

    <main class="app-main">
        @if(session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="flash flash-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

</body>
</html>
