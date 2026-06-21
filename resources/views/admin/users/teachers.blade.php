@extends('layouts.admin')
@section('title', 'Teachers')
@section('page-title', 'Teachers')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Teachers</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Teachers
        </div>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn-rupp-primary">
        <i class="bi bi-person-plus-fill"></i> Add Teacher
    </a>
</div>

{{-- Summary --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:28px;">
    <div class="stat-card">
        <div class="stat-card-icon gold"><i class="bi bi-person-workspace"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Teachers</div>
            <div class="value">{{ $totalTeachers }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="bi bi-diagram-3-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Departments</div>
            <div class="value">{{ $totalDepts }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="bi bi-building"></i></div>
        <div class="stat-card-info">
            <div class="label">Faculties</div>
            <div class="value">{{ $faculties->count() }}</div>
        </div>
    </div>
</div>

{{-- Faculty sections --}}
@forelse($faculties as $faculty)
@php $facultyTeachers = $faculty->departments->flatMap(fn($d) => $d->teachers); @endphp
@if($facultyTeachers->count() === 0) @continue @endif

<div style="margin-bottom:36px;">

    {{-- Faculty header --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:18px;">
        <div style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:10px; padding:8px 18px; font-size:14px; font-weight:700; display:flex; align-items:center; gap:8px; flex-shrink:0;">
            <i class="bi bi-building"></i> {{ $faculty->name }}
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af; white-space:nowrap;">
            {{ $facultyTeachers->count() }} teachers
        </span>
    </div>

    {{-- Departments --}}
    @foreach($faculty->departments as $dept)
    @if($dept->teachers->count() === 0) @continue @endif

    <div style="margin-bottom:20px; padding-left:18px; border-left:3px solid #e5e7eb;">

        {{-- Department label --}}
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <span style="font-size:13px; font-weight:600; color:#374151; display:flex; align-items:center; gap:6px;">
                <i class="bi bi-diagram-3-fill" style="color:var(--rupp-green);"></i>
                {{ $dept->name }}
                @if($dept->code)
                <span style="font-size:11px; color:#9ca3af; font-weight:400;">({{ $dept->code }})</span>
                @endif
            </span>
            @if($dept->head_name)
            <span style="font-size:11.5px; color:#9ca3af;">
                <i class="bi bi-person-fill" style="color:var(--rupp-gold);"></i>
                Head: {{ $dept->head_name }}
            </span>
            @endif
            <span style="font-size:11.5px; color:#9ca3af; margin-left:auto;">
                {{ $dept->teachers->count() }} teacher(s)
            </span>
        </div>

        {{-- Option 6: Full-width row list inside one card --}}
        <div style="background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden;">
            @foreach($dept->teachers as $teacher)
            @php
                $totalSections  = $teacher->sections->count();
                $inSession      = $teacher->sections->filter(fn($s) => $s->course?->semester?->isRunning())->count();
                $totalStudents  = $teacher->sections->flatMap(fn($s) => $s->enrollments->where('status','enrolled'))->count();
            @endphp

            <a href="{{ route('admin.users.edit', $teacher->user) }}"
               style="text-decoration:none; display:flex; align-items:center; gap:16px; padding:14px 18px; border-bottom:0.5px solid #f3f4f6; transition:background .1s; cursor:pointer;"
               onmouseover="this.style.background='#f9fafb'"
               onmouseout="this.style.background=''">

                {{-- Avatar --}}
                <div style="width:42px; height:42px; border-radius:50%; background:#fdf3d7; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:700; color:#92680a; flex-shrink:0; overflow:hidden;">
                    @if($teacher->user->photo)
                        <img src="{{ $teacher->user->photo_url }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                    @endif
                </div>

                {{-- Name + email --}}
                <div style="flex:1; min-width:0;">
                    <div style="font-size:13.5px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $teacher->user->name }}
                    </div>
                    @if($teacher->user->name_kh)
                    <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif; margin-top:1px;">
                        {{ $teacher->user->name_kh }}
                    </div>
                    @endif
                    <div style="font-size:11.5px; color:#9ca3af; margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $teacher->user->email }}
                        @if($teacher->employee_id)
                        <span style="color:#d1d5db; margin:0 4px;">·</span>
                        <span style="font-family:monospace;">{{ $teacher->employee_id }}</span>
                        @endif
                    </div>
                </div>

                {{-- Specialization --}}
                @if($teacher->specialization)
                <div style="flex-shrink:0; display:none;" class="teacher-spec">
                    <span style="font-size:11px; color:#6b7280; background:#f9fafb; border:0.5px solid #e5e7eb; border-radius:6px; padding:3px 10px;">
                        {{ Str::limit($teacher->specialization, 25) }}
                    </span>
                </div>
                @endif

                {{-- Stats --}}
                <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                    {{-- Sections --}}
                    <div style="text-align:center; min-width:48px;">
                        <div style="font-size:18px; font-weight:700; color:#374151; line-height:1;">{{ $totalSections }}</div>
                        <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em;">sections</div>
                    </div>

                    {{-- In session --}}
                    @if($inSession > 0)
                    <div style="text-align:center; min-width:48px; background:#f0fdf4; border-radius:8px; padding:4px 8px;">
                        <div style="font-size:18px; font-weight:700; color:#166534; line-height:1;">{{ $inSession }}</div>
                        <div style="font-size:10px; color:#16a34a; text-transform:uppercase; letter-spacing:.04em;">active</div>
                    </div>
                    @endif

                    {{-- Students --}}
                    <div style="text-align:center; min-width:48px;">
                        <div style="font-size:18px; font-weight:700; color:#374151; line-height:1;">{{ $totalStudents }}</div>
                        <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em;">students</div>
                    </div>

                    {{-- Status badge --}}
                    <span class="badge-rupp {{ $teacher->user->is_active ? 'badge-green' : 'badge-red' }}" style="font-size:10px; flex-shrink:0;">
                        <i class="bi bi-circle-fill" style="font-size:5px;"></i>
                        {{ $teacher->user->is_active ? 'Active' : 'Inactive' }}
                    </span>

                    {{-- Arrow --}}
                    <span style="color:#d1d5db; font-size:16px; flex-shrink:0;">›</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-person-workspace" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No teachers found.</div>
        <a href="{{ route('admin.users.create') }}" class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-person-plus-fill"></i> Add First Teacher
        </a>
    </div>
</div>
@endforelse
@endsection