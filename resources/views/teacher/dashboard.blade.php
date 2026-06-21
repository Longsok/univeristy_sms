@extends('layouts.teacher')
@section('title', 'Teacher Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 18 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        <div class="breadcrumb-text">
            {{ $teacher->department->name }} — {{ $teacher->department->faculty->name }}
        </div>
    </div>
</div>

{{-- Stats — all clickable --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:16px; margin-bottom:28px;">

    <a href="{{ route('teacher.courses.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon green"><i class="bi bi-collection-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">My Sections</div>
            <div class="value">{{ $sections->count() }}</div>
        </div>
    </a>

    <a href="{{ route('teacher.courses.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon gold"><i class="bi bi-people-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Students</div>
            <div class="value">{{ $sectionStats->sum(fn($s) => $s['enrolled_count']) }}</div>
        </div>
    </a>

    <a href="{{ route('teacher.courses.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon blue"><i class="bi bi-check2-circle"></i></div>
        <div class="stat-card-info">
            <div class="label">Graded Sections</div>
            <div class="value">{{ $sectionStats->filter(fn($s) => $s['graded_count'] > 0)->count() }}</div>
        </div>
    </a>

</div>

{{-- Sections grid --}}
<div style="margin-bottom:20px;">
    <h2 style="font-size:15px; font-weight:600; color:#111827; margin-bottom:14px;">My Courses This Semester</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px;">
        @forelse($sectionStats as $stat)
        @php $section = $stat['section']; @endphp
        <div class="card-rupp">
            <div style="background:var(--rupp-green); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                        {{ $section->course->code }}
                    </div>
                    <div style="color:#fff; font-size:14px; font-weight:600; margin-top:2px;">
                        {{ $section->course->name }}
                    </div>
                    <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:2px;">
                        {{ $section->name }}
                    </div>
                </div>
                <span style="background:rgba(201,162,39,0.2); color:var(--rupp-gold); font-size:11px; font-weight:600; padding:3px 8px; border-radius:20px; white-space:nowrap;">
                    {{ $section->course->credit_units }} credits
                </span>
            </div>
            <div class="card-rupp-body" style="padding:14px 16px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:13px; color:#6b7280;">
                    <span><i class="bi bi-people"></i> {{ $stat['enrolled_count'] }} students</span>
                    <span>
                        @if($stat['graded_count'] > 0)
                            <span class="badge-rupp badge-green" style="font-size:11px;">Graded</span>
                        @else
                            <span class="badge-rupp badge-gray" style="font-size:11px;">Not graded</span>
                        @endif
                    </span>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    {{-- FIXED: was teacher.attendance.qr, now teacher.attendance.index --}}
                    <a href="{{ route('teacher.attendance.index', $section) }}" class="btn-rupp-primary" style="padding:6px 12px; font-size:12px;">
                        <i class="bi bi-calendar-check-fill"></i> Attendance
                    </a>
                    <a href="{{ route('teacher.grades.index', $section) }}" class="btn-rupp-outline" style="padding:6px 12px; font-size:12px;">
                        <i class="bi bi-pencil-square"></i> Grades
                    </a>
                    <a href="{{ route('teacher.reports.index', $section) }}" class="btn-rupp-outline" style="padding:6px 12px; font-size:12px;">
                        <i class="bi bi-bar-chart"></i> Report
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="card-rupp" style="grid-column:1/-1;">
            <div class="card-rupp-body" style="text-align:center; padding:48px;">
                <i class="bi bi-collection" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:12px;"></i>
                <div style="font-size:15px; font-weight:500; color:#6b7280;">No sections assigned yet.</div>
                <div style="font-size:13px; color:#9ca3af; margin-top:4px;">Contact the administrator to get sections assigned.</div>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- Announcements --}}
@if($announcements->count())
<div class="card-rupp">
    <div class="card-rupp-header">
        <h5><i class="bi bi-megaphone-fill" style="color:var(--rupp-gold)"></i> Announcements</h5>
    </div>
    <div class="card-rupp-body" style="padding:0;">
        @foreach($announcements as $ann)
        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
            <div style="font-weight:600; font-size:13.5px; color:#111827;">{{ $ann->title }}</div>
            <div style="font-size:13px; color:#6b7280; margin-top:3px;">{{ Str::limit($ann->body, 120) }}</div>
            <div style="font-size:11px; color:#9ca3af; margin-top:5px;">{{ $ann->published_at->diffForHumans() }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection