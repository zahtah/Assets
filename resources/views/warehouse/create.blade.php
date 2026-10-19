@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>ثبت مال جدید در انبار</h1>
        <p>مال بدون مالک ثبت می‌شود و بعداً از صفحه‌ی انبار می‌توان آن را به کاربر اختصاص داد.</p>
    </div>
    <a href="{{ route('warehouse.index') }}" class="btn btn-ghost">بازگشت به انبار</a>
</div>

<div class="card" style="max-width:640px;">
    <form method="POST" action="{{ route('warehouse.assets.store') }}" class="card-body">
        @csrf

        @php
            $label = 'display:block;font-size:.82rem;color:#475569;margin-bottom:5px;font-weight:600;';
            $err = 'color:#b91c1c;font-size:.78rem;margin-top:4px;';
        @endphp

        <div style="margin-bottom:16px;">
            <label for="title" style="{{ $label }}">عنوان مال</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required autofocus style="width:100%;">
            @error('title') <div style="{{ $err }}">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:16px;">
            <div style="flex:2 1 220px;">
                <label for="asset_number" style="{{ $label }}">شماره اموال</label>
                <input type="text" id="asset_number" name="asset_number" value="{{ old('asset_number') }}" required style="width:100%;">
                @error('asset_number') <div style="{{ $err }}">{{ $message }}</div> @enderror
            </div>
            <div style="flex:1 1 120px;">
                <label for="count" style="{{ $label }}">تعداد</label>
                <input type="number" id="count" name="count" min="1" value="{{ old('count', 1) }}" style="width:100%;">
                @error('count') <div style="{{ $err }}">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label for="description" style="{{ $label }}">توضیحات</label>
            <input type="text" id="description" name="description" value="{{ old('description') }}" style="width:100%;">
            @error('description') <div style="{{ $err }}">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <button type="submit" class="btn btn-primary">ثبت</button>
            <button type="submit" name="add_another" value="1" class="btn btn-soft">ثبت و افزودن مال بعدی</button>
            <a href="{{ route('warehouse.index') }}" class="btn btn-ghost">انصراف</a>
        </div>
    </form>
</div>

@endsection
