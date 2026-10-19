@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1>اموال {{ $user->name }}</h1>
        <p>
            {{ $user->unit ?: '—' }}
            @if($user->department) · {{ $user->department }} @endif
            @if($user->room_number) · اتاق {{ $user->room_number }} @endif
            @if($user->floor) · {{ $user->floor }} @endif
            · {{ $assets->count() }} مال
        </p>
    </div>
    @if(auth()->user()->role === 'admin')
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <a href="{{ route('admin.users.assets.create', $user) }}" class="btn btn-primary">+ افزودن مال جدید</a>
            <a href="{{ route('users.contract', $user->id) }}" target="_blank" class="btn btn-ghost">تولید فرم اموال</a>
            <a href="{{ route('all-users') }}" class="btn btn-ghost">بازگشت به کاربران</a>
        </div>
    @endif
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
                    <th>تایید ادمین</th>
                    <th>تایید کاربر</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    @php $isHighlighted = isset($highlightId) && (int) $highlightId === (int) $asset->id; @endphp
                    <tr id="asset-{{ $asset->id }}" style="scroll-margin-top:80px;{{ $isHighlighted ? 'background-color:#fffbeb;box-shadow:inset -4px 0 0 #f59e0b;' : '' }}">
                        <td>
                            <div style="font-weight:600;">{{ $asset->title }}</div>
                            <div class="muted">{{ $asset->asset_number }}</div>
                        </td>
                        <td>{{ $asset->count }}</td>
                        <td>{{ $asset->description ?: '—' }}</td>
                        <td>{{ \App\Support\Jalali::date($asset->updated_at_date) }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                @if ($asset->admin_approved)
                                    <span class="badge badge-success">تایید شده</span>
                                @else
                                    <span class="badge badge-neutral">تایید نشده</span>
                                @endif
                                <form action="{{ route('assets.toggleAdminApproval', $asset) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $asset->admin_approved ? 'btn-danger-ghost' : 'btn-soft' }}">
                                        {{ $asset->admin_approved ? 'لغو تایید' : 'تایید' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td>
                            @if ($asset->user_approved)
                                <span class="badge badge-success">تایید شده</span>
                            @else
                                <span class="badge badge-neutral">تایید نشده</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.users.assets.edit', [$user, $asset]) }}" class="btn btn-ghost btn-sm">ویرایش</a>
                                <a href="{{ route('admin.users.assets.comment',[$user, $asset]) }}" class="btn btn-ghost btn-sm">
                                    کامنت
                                    @if ($asset->admin_new_comment)<span class="badge badge-danger">جدید</span>@endif
                                </a>
                                <form action="{{ route('admin.users.assets.destroy', [$user, $asset]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-ghost btn-sm"
                                            onclick="return confirm('آیا مطمئن هستید که می‌خواهید این مال را حذف کنید؟')">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    {{-- تغییرات و کامنت‌های این مال --}}
                    @if($asset->edits->count() > 0 || $asset->comments->count() > 0 || $isHighlighted)
                        <tr @if($isHighlighted) style="background-color:#fffbeb;box-shadow:inset -4px 0 0 #f59e0b;" @endif>
                            <td colspan="7" style="padding-top:6px;padding-bottom:10px;">
                                <details @if($isHighlighted) open @endif>
                                    <summary style="cursor:pointer;font-size:.82rem;font-weight:600;color:#1d4ed8;">
                                        تغییرات و کامنت‌ها
                                        <span class="muted">({{ $asset->edits->count() }} تغییر، {{ $asset->comments->count() }} کامنت)</span>
                                    </summary>

                                    <div style="display:flex;flex-wrap:wrap;gap:20px;margin-top:12px;">
                                        {{-- تغییرات --}}
                                        <div style="flex:1 1 360px;">
                                            <div style="font-weight:700;font-size:.85rem;margin-bottom:8px;">تاریخچه‌ی تغییرات (۵ مورد آخر)</div>
                                            @forelse($asset->edits->take(5) as $edit)
                                                <div style="border:1px solid #e2e8f0;border-radius:10px;padding:9px 12px;margin-bottom:8px;background:#fff;">
                                                    <div style="display:flex;justify-content:space-between;gap:8px;font-size:.78rem;color:#64748b;">
                                                        <span>{{ $edit->action_label }} — {{ $edit->editor?->name ?? 'نامشخص' }}</span>
                                                        <span>{{ \App\Support\Jalali::dateTime($edit->created_at) }}</span>
                                                    </div>
                                                    @if($edit->action === 'assign')
                                                        <div style="margin-top:4px;font-size:.85rem;">
                                                            به «{{ $edit->owner?->name ?? '—' }}» اختصاص داده شد
                                                            ({{ $edit->previousOwner ? 'قبلاً: ' . $edit->previousOwner->name : 'قبلاً در انبار بود' }})
                                                        </div>
                                                    @endif
                                                    @foreach(($edit->changes ?? []) as $field => $pair)
                                                        @php
                                                            $old = $pair[0] ?? null;
                                                            $new = $pair[1] ?? null;
                                                            if ($field === 'updated_at_date') {
                                                                $old = \App\Support\Jalali::date($old);
                                                                $new = \App\Support\Jalali::date($new);
                                                            }
                                                        @endphp
                                                        <div style="margin-top:4px;font-size:.85rem;">
                                                            {{ \App\Models\AssetEdit::FIELD_LABELS[$field] ?? $field }}:
                                                            از <span style="color:#b91c1c;text-decoration:line-through;">{{ ($old === null || $old === '') ? '—' : $old }}</span>
                                                            به <strong style="color:#15803d;">{{ ($new === null || $new === '') ? '—' : $new }}</strong>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @empty
                                                <div class="muted">تغییری ثبت نشده است.</div>
                                            @endforelse
                                        </div>

                                        {{-- کامنت‌ها --}}
                                        <div style="flex:1 1 360px;">
                                            <div style="font-weight:700;font-size:.85rem;margin-bottom:8px;">کامنت‌ها</div>
                                            @forelse($asset->comments as $comment)
                                                <div style="border:1px solid #e2e8f0;border-radius:10px;padding:9px 12px;margin-bottom:8px;background:#fff;">
                                                    <div style="display:flex;justify-content:space-between;gap:8px;font-size:.78rem;color:#64748b;">
                                                        <span style="font-weight:700;color:#0f172a;">{{ $comment->user?->name ?? 'نامشخص' }}</span>
                                                        <span>{{ \App\Support\Jalali::dateTime($comment->created_at) }}</span>
                                                    </div>
                                                    <div style="margin-top:4px;font-size:.88rem;white-space:pre-line;">{{ $comment->comment }}</div>
                                                </div>
                                            @empty
                                                <div class="muted">کامنتی ثبت نشده است.</div>
                                            @endforelse
                                            <a href="{{ route('admin.users.assets.comment',[$user, $asset]) }}" class="btn btn-soft btn-sm" style="margin-top:4px;">ثبت کامنت / پاسخ</a>
                                        </div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="7"><div class="empty-state">هنوز مالی ثبت نشده است.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <form action="{{ route('users.update_contract_button', $user->id) }}" method="POST" class="card-body"
          style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
        @csrf
        @method('PATCH')
        <label style="display:inline-flex;align-items:center;gap:10px;cursor:pointer;">
            <input type="checkbox" name="show_contract_button" value="1" @if($user->show_contract_button) checked @endif>
            <span>نمایش دکمه‌ی «مشاهده فرم اموال» به این کاربر</span>
        </label>
        <button type="submit" class="btn btn-ghost btn-sm">ذخیره</button>
    </form>
</div>

@endsection
