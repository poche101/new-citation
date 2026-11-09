<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0B1633; }
        h1 { font-size: 20px; margin: 0 0 4px; color: #0E2C73; }
        .meta { color: #64748b; margin-bottom: 18px; }
        .report { margin-bottom: 20px; padding-top: 12px; border-top: 2px solid #E5A913; page-break-inside: auto; }
        h2 { font-size: 14px; margin: 0 0 2px; color: #0E2C73; }
        .type { color: #8A6200; font-weight: bold; margin-bottom: 6px; }
        .facts { color: #334155; margin-bottom: 8px; line-height: 1.5; }
        h3 { font-size: 11px; margin: 10px 0 2px; color: #0E2C73; }
        .text { white-space: pre-wrap; line-height: 1.5; }
        .note { margin-top: 8px; color: #475569; font-style: italic; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">{{ $reports->count() }} submission(s). Exported {{ now()->format('j F Y') }}.</p>

    @forelse ($reports as $r)
        <div class="report">
            <h2>{{ $r->fullName() }}</h2>
            <p class="type">{{ $r->label() }}</p>
            <p class="facts">
                {{ $r->entityLabel() }}: {{ $r->entity_name }}<br>
                Unit: {{ $r->unit }}. Designation: {{ $r->designation }}<br>
                Kingschat: {{ $r->kingschat }}. Phone: {{ $r->phone }}<br>
                Period: {{ $r->periodText() }}. Status: {{ $r->approved ? 'Approved' : 'Pending' }}
            </p>

            @foreach ($r->sections() as [$heading, $text])
                <h3>{{ $heading }}</h3>
                <div class="text">{{ $text }}</div>
            @endforeach

            @if ($r->attachments->isNotEmpty())
                <p class="note">Files: {{ $r->attachments->pluck('original_name')->implode(', ') }}</p>
            @endif
            @if ($r->admin_comment)
                <p class="note">Admin comment: {{ $r->admin_comment }}</p>
            @endif
        </div>
    @empty
        <p>No submissions match this selection.</p>
    @endforelse
</body>
</html>
