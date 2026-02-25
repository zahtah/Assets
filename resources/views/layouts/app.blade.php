<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سیستم اموال شرکت</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-100">

<nav class="bg-white shadow p-4 flex justify-between">
    <div class="flex items-center gap-4">
        @if(auth()->user()->role === 'user')
            <a href="{{ route('assets.index') }}"
            class="px-4 py-2 bg-blue-600 text-gray-800 font-bold rounded-lg shadow hover:bg-blue-700 transition">
                سیستم اموال
            </a>
        @endif
        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.alerts.index') }}"
            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg shadow hover:bg-gray-300 transition">
                هشدارها
            </a>
        @endif
    </div>
    
    <div class="flex items-center gap-4">
        <span>سلام {{ auth()->user()->name }}</span>

        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users') }}" class="text-blue-600 px-4 py-2 bg-gray-200 font-bold rounded-lg shadow hover:bg-blue-700 transition">پنل ادمین</a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-red-600 px-4 py-2 bg-gray-200 font-bold rounded-lg shadow hover:bg-blue-700 transition">خروج</button>
        </form>
    </div>
</nav>

<div class="container mx-auto p-6">
    @if(session('success'))
        <div class="bg-green-200 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @yield('content')
</div>

</body>
</html>