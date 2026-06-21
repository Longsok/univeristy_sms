@extends('layouts.admin')
@section('title', 'Courses')
@section('page-title', 'Courses')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Courses</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Courses
        </div>
    </div>
    <a href="{{ route('admin.courses.create') }}" class="btn-rupp-primary">
        <i class="bi bi-plus-lg"></i> Add Course
    </a>
</div>

{{-- Summary stats --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="bi bi-book-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Courses</div>
            <div class="value">{{ $totalCourses }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon gold"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Programs</div>
            <div class="value">{{ $programs->count() }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="bi bi-building"></i></div>
        <div class="stat-card-info">
            <div class="label">Departments</div>
            <div class="value">{{ $programs->pluck('department_id')->unique()->count() }}</div>
        </div>
    </div>
</div>

{{-- Programs grouped by faculty --}}
@forelse($programsByFaculty as $facultyName => $facultyPrograms)
<div style="margin-bottom:28px;">

    {{-- Faculty label --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
        <div style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px;">
            <i class="bi bi-building"></i> {{ $facultyName }}
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af;">{{ $facultyPrograms->count() }} programs</span>
    </div>

    {{-- Program cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:14px;">
        @foreach($facultyPrograms as $program)
        @php
            $courseCount = $program->courses_count ?? 0;
            $years       = $program->courses->pluck('year_level')->unique()->sort()->values();
        @endphp
        <div class="card-rupp" style="transition:box-shadow .15s;"
             onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
             onmouseout="this.style.boxShadow=''">

            {{-- Card header --}}
            <div style="background:var(--rupp-green); padding:16px 18px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                            {{ $program->code }}
                        </div>
                        <div style="color:#fff; font-size:14px; font-weight:600; margin-top:3px; line-height:1.3;">
                            {{ $program->name }}
                        </div>
                        <div style="color:rgba(255,255,255,0.55); font-size:11.5px; margin-top:3px;">
                            {{ $program->department->name }}
                        </div>
                    </div>
                    <div style="background:rgba(201,162,39,0.2); border-radius:8px; padding:8px 12px; text-align:center; flex-shrink:0; margin-left:10px;">
                        <div style="font-size:22px; font-weight:700; color:var(--rupp-gold); line-height:1;">{{ $courseCount }}</div>
                        <div style="font-size:9px; color:rgba(255,255,255,0.5); text-transform:uppercase; margin-top:1px;">courses</div>
                    </div>
                </div>
            </div>

            {{-- Card body --}}
            <div style="padding:14px 18px;">
                {{-- Year badges --}}
                @if($years->count() > 0)
                <div style="display:flex; gap:5px; flex-wrap:wrap; margin-bottom:12px;">
                    @foreach($years as $year)
                    @php
                        $yearCount = $program->courses->where('year_level', $year)->count();
                    @endphp
                    <span style="background:#f0fdf4; border:1px solid #86efac; border-radius:6px; padding:3px 10px; font-size:11.5px; color:#166534;">
                        Year {{ $year }}
                        <span style="color:#9ca3af; margin-left:3px;">{{ $yearCount }}</span>
                    </span>
                    @endforeach
                </div>
                @else
                <div style="font-size:12.5px; color:#9ca3af; margin-bottom:12px;">No courses yet</div>
                @endif

                {{-- Degree level --}}
                <div style="font-size:12px; color:#6b7280; margin-bottom:14px;">
                    <i class="bi bi-award" style="color:var(--rupp-gold);"></i>
                    {{ ucfirst($program->degree_level ?? 'Bachelor') }}
                    @if($program->total_credits)
                    · {{ $program->total_credits }} credits total
                    @endif
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('admin.courses.program', $program) }}"
                       class="btn-rupp-primary"
                       style="flex:1; justify-content:center; padding:7px 14px; font-size:13px;">
                        <i class="bi bi-eye-fill"></i> View Courses
                    </a>
                    <a href="{{ route('admin.courses.create', ['program_id' => $program->id]) }}"
                       class="btn-rupp-outline"
                       style="padding:7px 12px; font-size:13px;"
                       title="Add course to this program">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-mortarboard" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No programs found.</div>
        <div style="font-size:13px; margin-top:4px;">Add faculties, departments and programs first.</div>
    </div>
</div>
@endforelse
@endsection