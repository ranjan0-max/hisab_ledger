{{-- Layout for public pages opened from signed share links. Expects $paginator (the page's rows). --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title')</title>
    <style>
        body { margin: 0; padding: 16px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #1e293b; background: #ffffff; }
        .wrap { max-width: 900px; margin: 0 auto; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 10px; margin-bottom: 14px; }
        .company { font-size: 18px; font-weight: bold; color: #4f46e5; }
        .muted { color: #64748b; font-size: 12px; }
        .info { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 14px; }
        .party-name { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .balance { font-size: 16px; font-weight: bold; }
        .due { color: #dc2626; }
        .advance { color: #16a34a; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; white-space: nowrap; }
        th { background: #4f46e5; color: #ffffff; font-size: 12px; }
        td.desc { white-space: normal; min-width: 140px; }
        .text-right { text-align: right; }
        .footer { margin-top: 14px; text-align: center; }
        .actions { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .page-info { margin: 10px 0; font-size: 13px; color: #475569; }
        .pager { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; align-items: center; margin-top: 14px; }
        .pager a, .pager span.disabled { display: inline-block; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #4f46e5; font-weight: bold; font-size: 13px; }
        .pager span.disabled { color: #cbd5e1; }
        .pager .current { font-size: 13px; color: #475569; padding: 0 6px; }
        .download-btn { background: #4f46e5; color: #ffffff; border: 0; border-radius: 6px; padding: 10px 16px; font-size: 14px; font-weight: bold; cursor: pointer; }

        /* Browser "Save as PDF": print exactly what's on the page, without the button */
        @media print {
            @page { margin: 12mm; }
            body { padding: 0; font-size: 11px; }
            .no-print { display: none !important; }
            .table-scroll { overflow: visible; }
            th, td { white-space: normal; padding: 4px 6px; }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
            th, .info { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="actions no-print">
        <button type="button" class="download-btn" onclick="window.print()">
            Download PDF{{ $paginator->lastPage() > 1 ? ' (This Page)' : '' }}
        </button>
    </div>

    <div class="header">
        <div class="company">@yield('company')</div>
        <div class="muted">@yield('subtitle')</div>
    </div>

    @yield('summary')

    @if($paginator->count() > 0)
        <div class="page-info">
            Showing entries {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
            @if($paginator->lastPage() > 1)
                · Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            @endif
        </div>
    @endif

    <div class="table-scroll">
        @yield('table')
    </div>

    @if($paginator->lastPage() > 1)
        <div class="pager no-print">
            @if($paginator->onFirstPage())
                <span class="disabled">&laquo; First</span>
                <span class="disabled">&lsaquo; Prev</span>
            @else
                <a href="{{ $paginator->url(1) }}">&laquo; First</a>
                <a href="{{ $paginator->previousPageUrl() }}">&lsaquo; Prev</a>
            @endif
            <span class="current">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}">Next &rsaquo;</a>
                <a href="{{ $paginator->url($paginator->lastPage()) }}">Last &raquo;</a>
            @else
                <span class="disabled">Next &rsaquo;</span>
                <span class="disabled">Last &raquo;</span>
            @endif
        </div>
    @endif

    <div class="footer muted">
        Generated on {{ now()->format('d M Y, h:i A') }} · Powered by {{ config('app.name', 'Hisab Ledger') }}
    </div>
</div>
</body>
</html>
