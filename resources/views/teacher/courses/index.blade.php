@extends('layouts.teacher')
@section('title', 'My Courses')
@section('page-title', 'My Courses')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>My Courses</h1>
        <div class="breadcrumb-text">Sections assigned to you this semester</div>
    </div>
</div>

@forelse($sections as $section)
@php $enrolled = $section->enrollments->where('status','enrolled')->count(); @endphp
<div class="card-rupp" style="margin-bottom:16px;">
    <div style="background:var(--rupp-green);padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="color:var(--rupp-gold);font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;">
                {{ $section->course->code }} — {{ $section->name }}
            </div>
            <div style="color:#fff;font-size:16px;font-weight:600;margin-top:3px;">
                {{ $section->course->name }}
            </div>
            <div style="color:rgba(255,255,255,0.6);font-size:12px;margin-top:2px;">
                {{ $section->course->department->name }}
            </div>
        </div>
        <span style="background:rgba(201,162,39,0.2);color:var(--rupp-gold);font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;">
            {{ $section->course->credit_units }} credits
        </span>
    </div>

    <div class="card-rupp-body">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">

            {{-- Info --}}
            <div style="display:flex;gap:20px;font-size:13px;color:#6b7280;">
                <span>
                    <i class="bi bi-people-fill" style="color:var(--rupp-green);"></i>
                    {{ $enrolled }} students
                </span>
                @foreach($section->timetables as $tt)
                <span>
                    <i class="bi bi-clock" style="color:var(--rupp-green);"></i>
                    {{ $tt->day_of_week }}, {{ substr($tt->start_time,0,5) }}–{{ substr($tt->end_time,0,5) }}
                    @if($tt->room) · {{ $tt->room }} @endif
                </span>
                @endforeach
            </div>

            {{-- Action buttons --}}
            <div style="display:flex;gap:8px;flex-wrap:wrap;">

                {{-- Students --}}
                <a href="{{ route('teacher.courses.students', $section) }}"
                   class="btn-rupp-outline" style="padding:6px 14px;font-size:12px;">
                    <i class="bi bi-people"></i> Students
                </a>

                {{-- Attendance (manual weekly grid) --}}
                <a href="{{ route('teacher.attendance.index', $section) }}"
                   class="btn-rupp-primary" style="padding:6px 14px;font-size:12px;">
                    <i class="bi bi-calendar-check-fill"></i> Attendance
                </a>

                {{-- Grades — disabled if no students --}}
                @if($enrolled > 0)
                <a href="{{ route('teacher.grades.index', $section) }}"
                   class="btn-rupp-outline" style="padding:6px 14px;font-size:12px;">
                    <i class="bi bi-pencil-square"></i> Grades
                </a>

                @php
                    $reexamCount = $section->enrollments()
                        ->where('grade_status', 'reexam')
                        ->count();
                @endphp
                @if($reexamCount > 0)
                <a href="{{ route('teacher.reexam.index', $section) }}"
                class="btn-rupp-outline" style="padding:6px 14px;font-size:12px; position:relative;">
                    <i class="bi bi-arrow-repeat" style="color:#f59e0b;"></i> Re-Exam
                    <span style="position:absolute; top:-6px; right:-6px; background:#ef4444; color:#fff; border-radius:50%; width:16px; height:16px; font-size:9px; display:flex; align-items:center; justify-content:center; font-weight:700;">
                        {{ $reexamCount }}
                    </span>
                </a>
                @endif
                
                <a href="{{ route('teacher.groups.index', $section) }}"
                   class="btn-rupp-outline" style="padding:6px 14px;font-size:12px;">
                    <i class="bi bi-people-fill"></i> Groups
                </a>
                @else
                <span style="padding:6px 14px;font-size:12px;background:#f3f4f6;color:#9ca3af;border-radius:8px;display:inline-flex;align-items:center;gap:6px;cursor:not-allowed;" title="No students enrolled">
                    <i class="bi bi-pencil-square"></i> Grades
                </span>
                <span style="padding:6px 14px;font-size:12px;background:#f3f4f6;color:#9ca3af;border-radius:8px;display:inline-flex;align-items:center;gap:6px;cursor:not-allowed;" title="No students enrolled">
                    <i class="bi bi-people-fill"></i> Groups
                </span>
                @endif

                {{-- Report --}}
                <a href="{{ route('teacher.reports.index', $section) }}"
                   class="btn-rupp-outline" style="padding:6px 14px;font-size:12px;">
                    <i class="bi bi-bar-chart"></i> Report
                </a>

            </div>
        </div>
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center;padding:48px;">
        <i class="bi bi-book" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:12px;"></i>
        <div style="font-size:15px;font-weight:500;color:#6b7280;">No sections assigned yet.</div>
        <div style="font-size:13px;color:#9ca3af;margin-top:4px;">Contact the administrator to get sections assigned.</div>
    </div>
</div>
@endforelse
@endsection