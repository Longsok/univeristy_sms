@extends('layouts.teacher')
@section('title', 'Grade Report')
@section('page-title', 'Grade Report')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $section->course->name }} — Grade Report</h1>
        <div class="breadcrumb-text">
            {{ $section->course->code }} / {{ $section->name }}
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('teacher.reports.pdf', $section) }}" class="btn-rupp-primary" target="_blank">
            <i class="bi bi-file-earmark-pdf-fill"></i> PDF
        </a>
        <a href="{{ route('teacher.reports.excel', $section) }}" class="btn-rupp-outline">
            <i class="bi bi-file-earmark-excel-fill"></i> Excel
        </a>
    </div>
</div>

{{-- Summary cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:16px; margin-bottom:16px;">
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; text-align:center;">
        <div style="font-size:28px; font-weight:700; color:#111827;">{{ $summary['total'] }}</div>
        <div style="font-size:12px; color:#6b7280; margin-top:2px;">Total Students</div>
    </div>
    <div style="background:#dcfce7; border:1px solid #86efac; border-radius:12px; padding:18px; text-align:center;">
        <div style="font-size:28px; font-weight:700; color:#166534;">{{ $summary['pass'] }}</div>
        <div style="font-size:12px; color:#16a34a; margin-top:2px;">Pass</div>
    </div>
    <div style="background:#ffedd5; border:1px solid #fdba74; border-radius:12px; padding:18px; text-align:center;">
        <div style="font-size:28px; font-weight:700; color:#c2410c;">{{ $summary['reexam'] }}</div>
        <div style="font-size:12px; color:#ea580c; margin-top:2px;">Re-exam</div>
    </div>
    <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:12px; padding:18px; text-align:center;">
        <div style="font-size:28px; font-weight:700; color:#991b1b;">{{ $summary['fail'] }}</div>
        <div style="font-size:12px; color:#dc2626; margin-top:2px;">Fail</div>
    </div>
    <div style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:12px; padding:18px; text-align:center;">
        <div style="font-size:28px; font-weight:700; color:#6b7280;">{{ $summary['incomplete'] }}</div>
        <div style="font-size:12px; color:#9ca3af; margin-top:2px;">Incomplete</div>
    </div>
</div>

{{-- ← RE-EXAM BUTTON (shows only when reexam students exist) --}}
@if($summary['reexam'] > 0)
<div style="background:#fff7ed; border:1px solid #fdba74; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; align-items:center; gap:10px;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b; font-size:18px;"></i>
        <div>
            <div style="font-size:13.5px; font-weight:600; color:#c2410c;">
                {{ $summary['reexam'] }} {{ Str::plural('student', $summary['reexam']) }} eligible for Re-Exam
            </div>
            <div style="font-size:12px; color:#6b7280; margin-top:2px;">
                These students scored between 45–49. Enter their re-exam scores to update their status.
            </div>
        </div>
    </div>
    <a href="{{ route('teacher.reexam.index', $section) }}"
       class="btn-rupp-outline"
       style="font-size:13px; padding:8px 16px; border-color:#f59e0b; color:#c2410c; white-space:nowrap;">
        <i class="bi bi-arrow-repeat" style="color:#f59e0b;"></i>
        Manage Re-Exam
    </a>
</div>
@endif

{{-- Grade distribution --}}
@if($gradeDistribution->count())
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-header">
        <h5><i class="bi bi-bar-chart-fill" style="color:var(--rupp-gold)"></i> Grade Distribution</h5>
    </div>
    <div class="card-rupp-body">
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            @foreach(['A','B','C','D','F'] as $g)
            @php $count = $gradeDistribution->get($g, 0); @endphp
            <div style="text-align:center; min-width:80px;">
                <div style="height:{{ max(20, $count * 16) }}px; background:{{ $g === 'A' ? '#1e4d2b' : ($g === 'B' ? '#2a6b3c' : ($g === 'C' ? '#c9a227' : ($g === 'D' ? '#f97316' : '#ef4444'))) }}; border-radius:6px 6px 0 0; display:flex; align-items:flex-start; justify-content:center; padding-top:4px;">
                    <span style="font-size:12px; font-weight:600; color:#fff;">{{ $count }}</span>
                </div>
                <div style="font-size:14px; font-weight:700; color:#111827; margin-top:6px;">{{ $g }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Per-status tables --}}
@foreach([
    ['pass',       'Pass',       'badge-green',  'bi-check-circle-fill',  '#166534'],
    ['reexam',     'Re-Exam',    'badge-orange', 'bi-arrow-repeat',       '#c2410c'],
    ['fail',       'Fail',       'badge-red',    'bi-x-circle-fill',      '#991b1b'],
    ['incomplete', 'Incomplete', 'badge-gray',   'bi-hourglass-split',    '#6b7280'],
] as [$status, $label, $badgeClass, $icon, $color])

@if($grouped->has($status) && $grouped->get($status)->count())
<div class="card-rupp" style="margin-bottom:16px;">
    <div class="card-rupp-header">
        <h5>
            <i class="bi {{ $icon }}" style="color:{{ $color }}"></i>
            {{ $label }} Students
            <span class="badge-rupp {{ $badgeClass }}" style="margin-left:6px;">
                {{ $grouped->get($status)->count() }}
            </span>
        </h5>
        {{-- Re-exam button inside the reexam table header --}}
        @if($status === 'reexam')
        <a href="{{ route('teacher.reexam.index', $section) }}"
           class="btn-rupp-outline"
           style="font-size:12px; padding:5px 12px; border-color:#fdba74; color:#c2410c;">
            <i class="bi bi-arrow-repeat" style="color:#f59e0b;"></i> Enter Re-Exam Scores
        </a>
        @endif
    </div>
    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th style="text-align:center;">Final Score</th>
                    <th style="text-align:center;">Letter</th>
                    <th style="text-align:center;">GPA Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grouped->get($status) as $enrollment)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $enrollment->student->user->name }}</div>
                        @if($enrollment->student->user->name_kh)
                        <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">
                            {{ $enrollment->student->user->name_kh }}
                        </div>
                        @endif
                    </td>
                    <td style="font-family:monospace; font-size:12.5px; color:#6b7280;">
                        {{ $enrollment->student->student_id }}
                    </td>
                    <td style="text-align:center; font-weight:600; color:{{ $color }};">
                        {{ $enrollment->final_grade ? number_format($enrollment->final_grade, 2) : '—' }}
                    </td>
                    <td style="text-align:center;">
                        @if($enrollment->letter_grade)
                            <span class="badge-rupp {{ $badgeClass }}">{{ $enrollment->letter_grade }}</span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="text-align:center; color:#6b7280;">
                        {{ $enrollment->grade_points ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

@endsection