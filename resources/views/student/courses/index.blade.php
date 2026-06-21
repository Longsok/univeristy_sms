@extends('layouts.student')
@section('title', 'My Courses')
@section('page-title', 'My Courses')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>My Courses</h1>
        <div class="breadcrumb-text">
            @if(isset($currentSemester) && $currentSemester)
                {{ $currentSemester->name }} {{ $currentSemester->academic_year }}
                @if($currentSemester->year_level)
                    · <span style="color:var(--rupp-gold);">Year {{ $currentSemester->year_level }}</span>
                @endif
            @else
                All enrolled courses
            @endif
        </div>
    </div>
</div>

@forelse($enrollments as $enrollment)
@php $section = $enrollment->section; $course = $section->course; @endphp
<div class="card-rupp" style="margin-bottom:16px;">
    <div style="background:var(--rupp-green); padding:16px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
            <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                {{ $course->code }}
            </div>
            <div style="color:#fff; font-size:16px; font-weight:600; margin-top:3px;">
                {{ $course->name }}
            </div>
            <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:2px;">
                {{ $section->name }} &mdash; {{ $course->department->name }}
            </div>
        </div>
        <div style="text-align:right;">
            <span style="background:rgba(201,162,39,0.2); color:var(--rupp-gold); font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px;">
                {{ $course->credit_units }} Credits
            </span>
            @if($enrollment->grade_status && $enrollment->grade_status !== 'not_graded')
                <div style="margin-top:6px;">
                    <span class="badge-rupp {{ match($enrollment->grade_status) {
                        'pass'       => 'badge-green',
                        'reexam'     => 'badge-orange',
                        'fail'       => 'badge-red',
                        'incomplete' => 'badge-gray',
                        default      => 'badge-gray'
                    } }}">
                        {{ $enrollment->letter_grade ?? ucfirst($enrollment->grade_status) }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <div class="card-rupp-body">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

            {{-- Teacher --}}
            <div>
                <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:.05em;">Teacher</div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:var(--rupp-gold-pale);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#92680a;">
                        {{ strtoupper(substr($section->teacher?->user?->name ?? 'T', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#111827;">{{ $section->teacher?->user?->name ?? 'Not assigned' }}</div>
                        <div style="font-size:11px;color:#9ca3af;">{{ $section->teacher?->department?->name ?? '' }}</div>
                    </div>
                </div>
            </div>

            {{-- Schedule --}}
            <div>
                <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:.05em;">Schedule</div>
                @forelse($section->timetables as $timetable)
                    <div style="font-size:13px; color:#374151; display:flex; align-items:center; gap:6px; margin-bottom:3px;">
                        <i class="bi bi-clock" style="color:var(--rupp-green); font-size:12px;"></i>
                        {{ $timetable->day_of_week }}, {{ substr($timetable->start_time, 0, 5) }}–{{ substr($timetable->end_time, 0, 5) }}
                        <span style="color:#9ca3af;">({{ $timetable->room ?? 'TBA' }})</span>
                    </div>
                @empty
                    <div style="font-size:13px; color:#9ca3af;">No schedule set</div>
                @endforelse
            </div>

            {{-- Grade breakdown --}}
            <div>
                <div style="font-size:11px; color:#9ca3af; margin-bottom:4px; text-transform:uppercase; letter-spacing:.05em;">Grade Breakdown</div>
                @php
                    $components = $section->gradeComponents;
                    $grades     = $enrollment->grades->keyBy('grade_component_id');
                    $liveTotal  = 0;
                @endphp
                @forelse($components as $component)
                    @php
                        $grade = $grades->get($component->id);
                        $score = $grade ? ($grade->reexam_score ?? $grade->score) : null;
                        if ($score !== null) $liveTotal += $score;
                    @endphp
                    <div style="display:flex; justify-content:space-between; font-size:12.5px; margin-bottom:4px;">
                        <span style="color:#6b7280;">
                            {{ $component->name }}
                            <span style="font-size:11px; color:#9ca3af;">({{ $component->weight_percent }}pts)</span>
                        </span>
                        <span style="font-weight:600; color:{{ $score !== null ? '#111827' : '#9ca3af' }};">
                            {{-- FIXED: show score / weight_percent not score / max_score --}}
                            {{ $score !== null ? number_format($score, 1) . ' / ' . $component->weight_percent : '—' }}
                        </span>
                    </div>
                @empty
                    <div style="font-size:13px; color:#9ca3af;">Not configured yet</div>
                @endforelse

                {{-- Final grade --}}
                @if($enrollment->final_grade)
                    <div style="margin-top:8px; padding-top:8px; border-top:2px solid var(--rupp-green); display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:12.5px; font-weight:600; color:#374151;">Final Grade</span>
                        <span style="font-size:15px; font-weight:700; color:var(--rupp-green);">
                            {{ number_format($enrollment->final_grade, 1) }}
                            <span style="font-size:11px; color:#9ca3af; font-weight:400;">/ 100</span>
                        </span>
                    </div>
                @elseif($liveTotal > 0)
                    <div style="margin-top:8px; padding-top:8px; border-top:1px dashed #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:12px; color:#9ca3af;">Current Total</span>
                        <span style="font-size:14px; font-weight:600; color:#6b7280;">
                            {{ number_format($liveTotal, 1) }}
                            <span style="font-size:11px; color:#9ca3af; font-weight:400;">/ 100</span>
                        </span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px;">
        <i class="bi bi-book" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500; color:#6b7280;">You are not enrolled in any courses yet.</div>
        <div style="font-size:13px; color:#9ca3af; margin-top:4px;">Contact the admin office for enrollment.</div>
    </div>
</div>
@endforelse
@endsection