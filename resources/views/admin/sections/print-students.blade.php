<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List — {{ $section->course->code }} {{ $section->name }}</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#1f2937; padding:20px; }
        .header { text-align:center; margin-bottom:20px; padding-bottom:14px; border-bottom:2px solid #1e4d2b; }
        .header h1 { font-size:16px; font-weight:bold; color:#1e4d2b; }
        .header h2 { font-size:13px; color:#c9a227; margin-top:4px; }
        .header p  { font-size:11px; color:#6b7280; margin-top:3px; }
        .info-grid { display:flex; justify-content:space-between; margin-bottom:16px; font-size:11px; gap:10px; flex-wrap:wrap; }
        .info-box { background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:8px 14px; flex:1; min-width:120px; }
        .info-label { color:#9ca3af; font-size:10px; text-transform:uppercase; margin-bottom:2px; }
        .info-value { font-weight:bold; color:#111827; }
        table { width:100%; border-collapse:collapse; margin-bottom:16px; }
        th { background:#1e4d2b; color:#fff; padding:7px 10px; font-size:10px; font-weight:bold; text-transform:uppercase; text-align:left; }
        td { padding:8px 10px; border-bottom:1px solid #f3f4f6; font-size:11px; vertical-align:middle; }
        tr:nth-child(even) td { background:#f9fafb; }
        .sig-section { margin-top:30px; display:flex; justify-content:space-between; }
        .sig-box { text-align:center; }
        .sig-line { border-top:1px solid #374151; width:180px; margin:0 auto 4px; padding-top:6px; }
        .footer { margin-top:20px; text-align:center; font-size:10px; color:#9ca3af; border-top:1px solid #e5e7eb; padding-top:10px; }
        @media print {
            body { padding:10px; }
            .no-print { display:none; }
        }
    </style>
</head>
<body>

{{-- Print / Close buttons --}}
<div class="no-print" style="text-align:right; margin-bottom:16px;">
    <button onclick="window.print()"
        style="background:#1e4d2b; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-size:13px; cursor:pointer;">
        🖨️ Print
    </button>
    <button onclick="window.close()"
        style="background:#fff; color:#374151; border:1px solid #d1d5db; padding:8px 18px; border-radius:6px; font-size:13px; cursor:pointer; margin-left:8px;">
        Close
    </button>
</div>

<div class="header">
    <h1>Royal University of Phnom Penh</h1>
    <h2>Student Attendance / Enrollment List</h2>
    <p>Printed: {{ now()->format('d F Y, H:i') }}</p>
</div>

<div class="info-grid">
    <div class="info-box">
        <div class="info-label">Course</div>
        <div class="info-value">{{ $section->course->code }} — {{ $section->course->name }}</div>
    </div>
    <div class="info-box">
        <div class="info-label">Section</div>
        <div class="info-value">{{ $section->name }}</div>
    </div>
    <div class="info-box">
        <div class="info-label">Program</div>
        <div class="info-value">{{ $section->course->program?->code ?? '—' }}</div>
    </div>
    <div class="info-box">
        <div class="info-label">Teacher</div>
        <div class="info-value">{{ $section->teacher?->user?->name ?? 'N/A' }}</div>
    </div>
    <div class="info-box">
        <div class="info-label">Total Students</div>
        <div class="info-value">{{ $enrollments->count() }}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:36px;">#</th>
            <th>Student Name</th>
            <th>Student ID</th>
            <th style="width:60px; text-align:center;">Year</th>
            <th style="width:70px; text-align:center;">Batch</th>
            <th style="width:80px; text-align:center;">Signature</th>
        </tr>
    </thead>
    <tbody>
        @foreach($enrollments as $i => $enrollment)
        @php $student = $enrollment->student; @endphp
        <tr>
            <td style="color:#9ca3af;">{{ $i + 1 }}</td>
            <td>
                <div style="font-weight:500;">{{ $student->user->name }}</div>
                @if($student->user->name_kh)
                <div style="font-size:10px; color:#6b7280; font-family:serif;">
                    {{ $student->user->name_kh }}
                </div>
                @endif
            </td>
            <td style="font-family:monospace; color:#374151;">
                {{ $student->student_id }}
            </td>
            <td style="text-align:center; font-weight:600; color:#1e4d2b;">
                Year {{ $student->year_level }}
            </td>
            <td style="text-align:center;">
                @if($student->batch)
                    Batch {{ $student->batch }}
                @else
                    <span style="color:#d1d5db;">—</span>
                @endif
            </td>
            <td style="text-align:center; color:#d1d5db;">——</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="sig-section">
    <div class="sig-box">
        <div class="sig-line"></div>
        <div>Teacher Signature</div>
    </div>
    <div class="sig-box">
        <div class="sig-line"></div>
        <div>Department Head</div>
    </div>
    <div class="sig-box">
        <div class="sig-line"></div>
        <div>Dean</div>
    </div>
</div>

<div class="footer">
    Royal University of Phnom Penh — Student Management System — {{ now()->format('Y') }}
</div>
</body>
</html>