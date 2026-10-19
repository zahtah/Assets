{{-- صفحه‌بندی ساده (با استایل inline تا به build تیلویند وابسته نباشد) --}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last    = $paginator->lastPage();
        $start   = max(1, $current - 2);
        $end     = min($last, $current + 2);
        $btn     = 'display:inline-block;min-width:34px;padding:4px 10px;margin:0 2px;border:1px solid #d1d5db;border-radius:6px;text-align:center;background:#fff;color:#1f2937;text-decoration:none;';
        $active  = 'display:inline-block;min-width:34px;padding:4px 10px;margin:0 2px;border:1px solid #2563eb;border-radius:6px;text-align:center;background:#2563eb;color:#fff;';
        $off     = 'display:inline-block;min-width:34px;padding:4px 10px;margin:0 2px;border:1px solid #e5e7eb;border-radius:6px;text-align:center;background:#f9fafb;color:#9ca3af;';
    @endphp
    <nav style="margin-top:16px;text-align:center;" aria-label="صفحه‌بندی">
        @if ($paginator->onFirstPage())
            <span style="{{ $off }}">قبلی</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" style="{{ $btn }}">قبلی</a>
        @endif

        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}" style="{{ $btn }}">1</a>
            @if ($start > 2) <span>…</span> @endif
        @endif

        @for ($i = $start; $i <= $end; $i++)
            @if ($i === $current)
                <span style="{{ $active }}">{{ $i }}</span>
            @else
                <a href="{{ $paginator->url($i) }}" style="{{ $btn }}">{{ $i }}</a>
            @endif
        @endfor

        @if ($end < $last)
            @if ($end < $last - 1) <span>…</span> @endif
            <a href="{{ $paginator->url($last) }}" style="{{ $btn }}">{{ $last }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" style="{{ $btn }}">بعدی</a>
        @else
            <span style="{{ $off }}">بعدی</span>
        @endif
    </nav>
@endif
