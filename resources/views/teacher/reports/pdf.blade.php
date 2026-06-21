<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Report — {{ $section->course->code }}</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family: DejaVu Sans, sans-serif; font-size:11px; color:#1f2937; }
        .header { background:#1e4d2b; padding:16px 20px; }
        .header h1 { color:#c9a227; font-size:14px; font-weight:bold; }
        .header p { color:rgba(255,255,255,0.65); font-size:10px; margin-top:2px; }
        .gold-bar { background:#c9a227; height:3px; }
        .content { padding:16px 20px; }
        .section-info { display:flex; justify-content:space-between; margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #e5e7eb; }
        .info-label { font-size:9px; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:2px; }
        .info-value { font-size:12px; font-weight:bold; color:#111827; }
        .summary { display:flex; gap:12px; margin-bottom:16px; }
        .sum-box { flex:1; text-align:center; padding:10px; border-radius:6px; }
        .sum-pass   { background:#dcfce7; }
        .sum-reexam { background:#ffedd5; }
        .sum-fail   { background:#fee2e2; }
        .sum-inc    { background:#f3f4f6; }
        .sum-num  { font-size:22px; font-weight:bold; }
        .sum-label{ font-size:9px; margin-top:2px; }
        .section-title { font-size:9.5px; font-weight:bold; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin:14px 0 6px; border-bottom:1px solid #e5e7eb; padding-bottom:4px; }
        table { width:100%; border-collapse:collapse; margin-bottom:12px; }
        th { background:#f9fafb; padding:6px 8px; font-size:8.5px; font-weight:bold; text-transform:uppercase; color:#6b7280; border-bottom:1px solid #e5e7eb; text-align:left; }
        td { padding:7px 8px; font-size:10px; color:#374151; border-bottom:1px solid #f3f4f6; }
        .badge { display:inline-block; padding:2px 6px; border-radius:20px; font-size:8.5px; font-weight:bold; }
        .b-pass   { background:#dcfce7; color:#166534; }
        .b-reexam { background:#ffedd5; color:#c2410c; }
        .b-fail   { background:#fee2e2; color:#991b1b; }
        .b-gray   { background:#f3f4f6; color:#6b7280; }
        .footer { margin-top:20px; padding-top:12px; border-top:1px solid #e5e7eb; display:flex; justify-content:space-between; font-size:9px; color:#9ca3af; }
    </style>
</head>
<body>
<div class="header">
    <h1>Royal University of Phnom Penh — Grade Report</h1>
    <p>{{ $section->course->code }} {{ $section->course->name }} — {{ $section->name }}</p>
</div>
<div class="gold-bar"></div>
<div class="content">
 
    <div class="section-info">
        <div>
            <div class="info-label">Course</div>
            <div class="info-value">{{ $section->course->name }}</div>
        </div>
        <div>
            <div class="info-label">Section</div>
            <div class="info-value">{{ $section->name }}</div>
        </div>
        <div>
            <div class="info-label">Teacher</div>
            <div class="info-value">{{ $section->teacher?->user?->name ?? 'N/A' }}</div>
        </div>
        <div>
            <div class="info-label">Department</div>
            <div class="info-value">{{ $section->course->department->name }}</div>
        </div>
        <div>
            <div class="info-label">Generated</div>
            <div class="info-value">{{ now()->format('d M Y, H:i') }}</div>
        </div>
    </div>
 
    <div class="summary">
        <div class="sum-box sum-pass">
            <div class="sum-num" style="color:#166534;">{{ $summary['pass'] }}</div>
            <div class="sum-label" style="color:#16a34a;">Pass</div>
        </div>
        <div class="sum-box sum-reexam">
            <div class="sum-num" style="color:#c2410c;">{{ $summary['reexam'] }}</div>
            <div class="sum-label" style="color:#ea580c;">Re-exam</div>
        </div>
        <div class="sum-box sum-fail">
            <div class="sum-num" style="color:#991b1b;">{{ $summary['fail'] }}</div>
            <div class="sum-label" style="color:#dc2626;">Fail</div>
        </div>
        <div class="sum-box sum-inc">
            <div class="sum-num" style="color:#6b7280;">{{ $summary['incomplete'] }}</div>
            <div class="sum-label" style="color:#9ca3af;">Incomplete</div>
        </div>
        <div class="sum-box" style="background:#f0f9ff;">
            <div class="sum-num" style="color:#1e4d2b;">{{ $summary['total'] }}</div>
            <div class="sum-label" style="color:#2a6b3c;">Total</div>
        </div>
    </div>
 
    @foreach([
        ['pass',       'Pass Students',       'b-pass'],
        ['reexam',     'Re-Exam Students',    'b-reexam'],
        ['fail',       'Failed Students',     'b-fail'],
        ['incomplete', 'Incomplete',          'b-gray'],
    ] as [$status, $label, $badgeClass])
    @if($grouped->has($status) && $grouped->get($status)->count())
    <div class="section-title">{{ $label }} ({{ $grouped->get($status)->count() }})</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Student ID</th>
                <th style="text-align:center;">Final Grade</th>
                <th style="text-align:center;">Letter</th>
                <th style="text-align:center;">Points</th>
                <th style="text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped->get($status) as $i => $enrollment)
            <tr>
                <td style="color:#9ca3af;">{{ $i + 1 }}</td>
                <td style="font-weight:bold;">{{ $enrollment->student->user->name }}</td>
                <td style="font-family:monospace;color:#6b7280;">{{ $enrollment->student->student_id }}</td>
                <td style="text-align:center;font-weight:bold;">{{ $enrollment->final_grade ? number_format($enrollment->final_grade, 2) : '—' }}</td>
                <td style="text-align:center;">
                    @if($enrollment->letter_grade)
                    <span class="badge {{ $badgeClass }}">{{ $enrollment->letter_grade }}</span>
                    @else —
                    @endif
                </td>
                <td style="text-align:center;">{{ $enrollment->grade_points ?? '—' }}</td>
                <td style="text-align:center;"><span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endforeach
 
    <div class="footer">
        <div>Royal University of Phnom Penh — Academic Records</div>
        <div>{{ $section->course->code }} / {{ $section->name }} — Generated {{ now()->format('d M Y') }}</div>
    </div>
</div>
</body>
</html>
 