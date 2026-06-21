@extends('layouts.admin')
@section('title', 'Students')
@section('page-title', 'Students')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Students</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Students
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.students.import') }}" class="btn-rupp-outline">
            <i class="bi bi-file-earmark-excel-fill"></i> Import Students
        </a>
        <a href="{{ route('admin.users.create') }}" class="btn-rupp-primary">
            <i class="bi bi-person-plus-fill"></i> Add Student
        </a>
    </div>
</div>

{{-- Summary --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:28px;">
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="bi bi-people-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Students</div>
            <div class="value">{{ $totalStudents }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon gold"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Programs</div>
            <div class="value">{{ $totalPrograms }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="bi bi-building"></i></div>
        <div class="stat-card-info">
            <div class="label">Faculties</div>
            <div class="value">{{ $totalFaculties }}</div>
        </div>
    </div>
</div>

{{-- Faculty sections --}}
@forelse($faculties as $faculty)
@php
    $facultyTotal = $faculty->departments->flatMap(fn($d) => $d->programs)->sum('students_count');
@endphp

<div style="margin-bottom:36px;">

    {{-- Faculty header --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:18px;">
        <div style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:10px; padding:8px 18px; font-size:14px; font-weight:700; display:flex; align-items:center; gap:8px; flex-shrink:0;">
            <i class="bi bi-building"></i>
            {{ $faculty->name }}
            @if($faculty->code)
            <span style="font-size:11px; opacity:.7;">({{ $faculty->code }})</span>
            @endif
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af; white-space:nowrap;">
            {{ $facultyTotal }} students
        </span>
    </div>

    {{-- Departments --}}
    @foreach($faculty->departments->where('is_active', true) as $dept)
    @php $deptTotal = $dept->programs->sum('students_count'); @endphp
    @if($dept->programs->where('is_active', true)->count() === 0) @continue @endif

    <div style="margin-bottom:24px; padding-left:18px; border-left:3px solid #e5e7eb;">

        {{-- Department label --}}
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <span style="font-size:13px; font-weight:600; color:#374151; display:flex; align-items:center; gap:6px;">
                <i class="bi bi-diagram-3" style="color:var(--rupp-green);"></i>
                {{ $dept->name }}
            </span>
            <span style="font-size:11.5px; color:#9ca3af;">{{ $deptTotal }} students</span>
        </div>

        {{-- Program cards — Option 10 style --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:14px;">
            @foreach($dept->programs->where('is_active', true) as $program)
            @php
                $batchGroups = $program->students->groupBy('batch');
                $count       = $program->students_count ?? 0;
            @endphp

            <a href="{{ route('admin.students.program', $program) }}"
               style="text-decoration:none; display:block; border-radius:14px; overflow:hidden; border:0.5px solid rgba(30,77,43,0.25); transition:transform .15s, box-shadow .15s; background:#fff;"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'"
               onmouseout="this.style.transform=''; this.style.boxShadow=''">

                {{-- Gradient header with decorative circles --}}
                <div style="background:linear-gradient(135deg,#1e4d2b 0%,#2d7a45 100%); padding:20px 18px 18px; position:relative; overflow:hidden; height:160px; display:flex; flex-direction:column; justify-content:space-between;">

                    {{-- Decorative circles --}}
                    <div style="position:absolute; top:-18px; right:-18px; width:80px; height:80px; border-radius:50%; background:rgba(201,162,39,0.18); pointer-events:none;"></div>
                    <div style="position:absolute; bottom:-28px; left:14px; width:60px; height:60px; border-radius:50%; background:rgba(255,255,255,0.06); pointer-events:none;"></div>

                    {{-- Program code --}}
                    <div style="font-size:11px; font-weight:700; color:#c9a227; letter-spacing:.10em; text-transform:uppercase; margin-bottom:7px; position:relative;">
                        {{ $program->code }}
                    </div>

                    {{-- Program name --}}
                    <div style="font-size:14.5px; font-weight:600; color:#fff; line-height:1.35; margin-bottom:14px; position:relative;">
                        {{ $program->name }}
                    </div>

                    {{-- Student count --}}
                    <div style="position:relative; display:flex; align-items:flex-end; gap:6px;">
                        <div style="font-size:36px; font-weight:700; color:#fff; line-height:1;">{{ $count }}</div>
                        <div style="font-size:12px; color:rgba(255,255,255,0.5); padding-bottom:5px;">students</div>
                    </div>
                </div>

                {{-- Footer — batch pills + scholarship count --}}
                @php
                    $scholarshipCount = $program->students->whereIn('scholarship_type',['full','partial'])->count();
                @endphp
                <div style="padding:12px 16px; background:#fff; display:flex; justify-content:space-between; align-items:center; min-height:48px;">
                    <div style="display:flex; gap:5px; flex-wrap:wrap; align-items:center;">
                        @if($batchGroups->filter(fn($g,$k) => $k > 0)->count() > 0)
                            @foreach($batchGroups->filter(fn($g,$k) => $k > 0)->sortKeys() as $batch => $bs)
                            <span style="background:#dbeafe; color:#1e40af; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:500;">
                                Batch {{ $batch }} · {{ $bs->count() }}
                            </span>
                            @endforeach
                        @else
                            <span style="font-size:12px; color:#9ca3af;">No batches</span>
                        @endif
                        @if($scholarshipCount > 0)
                        <span style="background:#fef3c7; color:#92400e; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:500;">
                            ⭐ {{ $scholarshipCount }} scholarship
                        </span>
                        @endif
                    </div>
                    <span style="font-size:12px; color:#1e4d2b; font-weight:600; white-space:nowrap; margin-left:8px;">
                        View →
                    </span>
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
        <i class="bi bi-building" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No faculties found.</div>
    </div>
</div>
@endforelse
@endsection