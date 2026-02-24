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
    <div>
        <a href="{{ route('assets.index') }}" class="font-bold">سیستم اموال</a>
    </div>

    <div class="flex gap-4">
        <span>{{ auth()->user()->name }}</span>

        @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users') }}" class="text-blue-600">پنل ادمین</a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="text-red-600">خروج</button>
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