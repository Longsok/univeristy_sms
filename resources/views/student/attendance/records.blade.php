@extends('layouts.student')
@section('title', 'My Attendance')
@section('page-title', 'My Attendance')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>My Attendance</h1>
        <div class="breadcrumb-text">
            Attendance records for all enrolled courses
            @php $student = Auth::user()->student; @endphp
            @if($student?->batch)
            · <span style="color:var(--rupp-gold); font-weight:600;">Batch {{ $student->batch }}</span>
            @endif
        </div>
    </div>
</div>

@forelse($attendanceData as $data)
@php
    $section = $data['section'];
    $counts  = $data['counts'];
    $score   = $data['score'];
    $records = $data['records'];

    // Score color
    $scoreColor = $score >= 8 ? '#166534' : ($score >= 6 ? '#c2410c' : '#991b1b');
    $scoreBg    = $score >= 8 ? '#dcfce7' : ($score >= 6 ? '#ffedd5' : '#fee2e2');
@endphp

<div class="card-rupp" style="margin-bottom:20px;">

    {{-- Course header --}}
    <div style="background:var(--rupp-green); padding:14px 20px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                {{ $section->course->code }} — {{ $section->name }}
            </div>
            <div style="color:#fff; font-size:15px; font-weight:600; margin-top:2px;">
                {{ $section->course->name }}
            </div>
            <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:2px;">
                Teacher: {{ $section->teacher?->user?->name ?? 'N/A' }}
            </div>
        </div>

        {{-- Score badge --}}
        <div style="background:{{ $scoreBg }}; border-radius:12px; padding:12px 20px; text-align:center; min-width:80px;">
            <div style="font-size:24px; font-weight:700; color:{{ $scoreColor }}; line-height:1;">{{ $score }}</div>
            <div style="font-size:10px; color:{{ $scoreColor }}; font-weight:600;">/ 10 pts</div>
        </div>
    </div>

    {{-- Summary counts --}}
    <div style="display:flex; border-bottom:1px solid #f3f4f6;">
        <div style="flex:1; padding:12px; text-align:center; border-right:1px solid #f3f4f6;">
            <div style="font-size:20px; font-weight:700; color:#166534;">{{ $counts['present'] }}</div>
            <div style="font-size:11px; color:#9ca3af;">Present</div>
        </div>
        <div style="flex:1; padding:12px; text-align:center; border-right:1px solid #f3f4f6;">
            <div style="font-size:20px; font-weight:700; color:#991b1b;">{{ $counts['absent'] }}</div>
            <div style="font-size:11px; color:#9ca3af;">Absent</div>
        </div>
        <div style="flex:1; padding:12px; text-align:center; border-right:1px solid #f3f4f6;">
            <div style="font-size:20px; font-weight:700; color:#c2410c;">{{ $counts['late'] }}</div>
            <div style="font-size:11px; color:#9ca3af;">Late</div>
        </div>
        <div style="flex:1; padding:12px; text-align:center; border-right:1px solid #f3f4f6;">
            <div style="font-size:20px; font-weight:700; color:#1e40af;">{{ $counts['permission'] }}</div>
            <div style="font-size:11px; color:#9ca3af;">Permission</div>
        </div>
        <div style="flex:1; padding:12px; text-align:center;">
            <div style="font-size:20px; font-weight:700; color:#6b7280;">{{ $counts['total'] }}</div>
            <div style="font-size:11px; color:#9ca3af;">Recorded</div>
        </div>
    </div>

    {{-- Weekly grid (read-only) --}}
    <div style="padding:14px 16px; overflow-x:auto;">
        <div style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">
            Week by Week (16 Weeks)
        </div>
        <div style="display:flex; gap:5px; flex-wrap:wrap;">
            @foreach($weeks as $week)
            @php
                $record = $records->get($week);
                $status = $record?->status ?? '';
                [$bg, $color, $border] = match($status) {
                    'present'    => ['#dcfce7', '#166534', '#86efac'],
                    'absent'     => ['#fee2e2', '#991b1b', '#fca5a5'],
                    'late'       => ['#ffedd5', '#c2410c', '#fdba74'],
                    'permission' => ['#dbeafe', '#1e40af', '#93c5fd'],
                    default      => ['#f3f4f6', '#9ca3af', '#e5e7eb'],
                };
                $icon = match($status) {
                    'present'    => '✓',
                    'absent'     => '✗',
                    'late'       => '⏱',
                    'permission' => '📋',
                    default      => '—',
                };
            @endphp
            <div style="text-align:center; min-width:40px;">
                <div style="font-size:9px; color:#9ca3af; margin-bottom:3px;">W{{ $week }}</div>
                <div style="background:{{ $bg }}; color:{{ $color }}; border:1px solid {{ $border }}; border-radius:6px; width:38px; height:34px; display:flex; align-items:center; justify-content:center; font-size:13px;" title="Week {{ $week }}: {{ ucfirst($status ?: 'not recorded') }}">
                    {{ $icon }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Legend --}}
    <div style="padding:10px 16px; border-top:1px solid #f3f4f6; display:flex; gap:12px; flex-wrap:wrap;">
        <span style="font-size:11px; color:#166534; background:#dcfce7; border:1px solid #86efac; border-radius:4px; padding:2px 8px;">✓ Present (1pt)</span>
        <span style="font-size:11px; color:#991b1b; background:#fee2e2; border:1px solid #fca5a5; border-radius:4px; padding:2px 8px;">✗ Absent (0pt)</span>
        <span style="font-size:11px; color:#c2410c; background:#ffedd5; border:1px solid #fdba74; border-radius:4px; padding:2px 8px;">⏱ Late (0.5pt)</span>
        <span style="font-size:11px; color:#1e40af; background:#dbeafe; border:1px solid #93c5fd; border-radius:4px; padding:2px 8px;">📋 Permission (0.5pt)</span>
        <span style="font-size:11px; color:#9ca3af; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:4px; padding:2px 8px;">— Not recorded</span>
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-calendar-x" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No attendance records yet.</div>
        <div style="font-size:13px; margin-top:4px;">Your teacher will record attendance weekly.</div>
    </div>
</div>
@endforelse
@endsection