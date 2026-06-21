@extends('layouts.admin')
@section('title', 'Sections')
@section('page-title', 'Sections')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Sections</h1>
        <div class="breadcrumb-text"><a href="{{ route('admin.dashboard') }}">Dashboard</a> / Sections</div>
    </div>
    <a href="{{ route('admin.sections.create') }}" class="btn-rupp-primary">
        <i class="bi bi-plus-lg"></i> Add Section
    </a>
</div>
 
@if(session('success'))
    <div class="alert-success-rupp" style="margin-bottom:16px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif
 
{{-- Filter --}}
<div class="card-rupp" style="margin-bottom:16px;">
    <div class="card-rupp-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <select name="course_id" class="form-select-rupp" style="width:auto; min-width:220px;">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->code }} — {{ $course->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-rupp-primary" style="padding:8px 14px;">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if(request('course_id'))
                <a href="{{ route('admin.sections.index') }}" class="btn-rupp-outline" style="padding:7px 14px;">Clear</a>
            @endif
        </form>
    </div>
</div>
 
{{-- Sections grouped by Course --}}
@php
    $groupedSections = $sections->groupBy(fn($s) => $s->course->code . ' — ' . $s->course->name);
@endphp
 
@forelse($groupedSections as $courseName => $courseSections)
<div style="margin-bottom:24px;">
 
    {{-- Course header --}}
    @php $firstSection = $courseSections->first(); @endphp
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
        <div style="display:flex; align-items:center; gap:0;">
            {{-- Program badge --}}
            @if($firstSection->course->program)
            <div style="background:var(--rupp-gold); color:var(--rupp-green); border-radius:8px 0 0 8px; padding:5px 12px; font-size:11.5px; font-weight:600;">
                {{ $firstSection->course->program->code }}
            </div>
            @endif
            {{-- Course badge --}}
            <div style="background:var(--rupp-green); color:#fff; border-radius:{{ $firstSection->course->program ? '0 8px 8px 0' : '8px' }}; padding:5px 14px; font-size:12px; font-weight:600;">
                <i class="bi bi-book"></i> {{ $courseName }}
            </div>
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af;">{{ $courseSections->count() }} {{ Str::plural('section', $courseSections->count()) }}</span>
    </div>
 
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:12px;">
        @foreach($courseSections as $section)
        <div class="card-rupp">
            {{-- Section header --}}
            <div style="padding:12px 16px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--rupp-gold-pale); display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-collection-fill" style="color:var(--rupp-gold); font-size:16px;"></i>
                    </div>
                    <div>
                        <div style="font-size:14px; font-weight:700; color:var(--rupp-green);">{{ $section->name }}</div>
                        <div style="font-size:11px; color:#9ca3af;">Max {{ $section->max_students }} students</div>
                    </div>
                </div>
                @php $enrolled = $section->enrollments->where('status','enrolled')->count(); @endphp
                <span class="badge-rupp {{ $enrolled >= $section->max_students ? 'badge-red' : 'badge-green' }}" style="font-size:10px;">
                    {{ $enrolled }}/{{ $section->max_students }}
                </span>
            </div>
 
            <div class="card-rupp-body" style="padding:12px 16px;">
                {{-- Program --}}
                @if($section->course->program)
                <div style="font-size:12px; color:#6b7280; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-mortarboard-fill" style="color:var(--rupp-gold);"></i>
                    {{ $section->course->program->name }}
                </div>
                @endif
 
                {{-- Department --}}
                <div style="font-size:12px; color:#6b7280; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-diagram-3" style="color:var(--rupp-green);"></i>
                    {{ $section->course->department->name }}
                </div>
 
                {{-- Year level + Semester running status --}}
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                    @if($section->course->year_level)
                    <span style="font-size:12px; color:#6b7280; display:flex; align-items:center; gap:4px;">
                        <i class="bi bi-calendar3" style="color:#9ca3af;"></i>
                        Year {{ $section->course->year_level }}
                    </span>
                    @endif
                    @if($section->course->semester)
                        @if($section->course->semester->isRunning())
                        <span style="background:#dcfce7; color:#166534; border-radius:10px; padding:2px 8px; font-size:10px; font-weight:600;">
                            <i class="bi bi-circle-fill" style="font-size:5px;"></i> In Session
                        </span>
                        @elseif($section->course->semester->isUpcoming())
                        <span style="background:#dbeafe; color:#1e40af; border-radius:10px; padding:2px 8px; font-size:10px;">
                            Upcoming
                        </span>
                        @else
                        <span style="background:#f3f4f6; color:#6b7280; border-radius:10px; padding:2px 8px; font-size:10px;">
                            Completed
                        </span>
                        @endif
                    @endif
                </div>
 
                {{-- Teacher --}}
                <div style="background:#f9fafb; border-radius:8px; padding:8px 12px; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                    <div style="width:28px; height:28px; border-radius:50%; background:{{ $section->teacher ? 'var(--rupp-gold-pale)' : '#f3f4f6' }}; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:600; color:{{ $section->teacher ? '#92680a' : '#9ca3af' }}; flex-shrink:0;">
                        {{ $section->teacher ? strtoupper(substr($section->teacher->user->name, 0, 1)) : '?' }}
                    </div>
                    <div>
                        <div style="font-size:12.5px; font-weight:500; color:#374151;">
                            {{ $section->teacher?->user?->name ?? 'No teacher assigned' }}
                        </div>
                        @if($section->teacher)
                        <div style="font-size:10.5px; color:#9ca3af;">{{ $section->teacher->employee_id }}</div>
                        @endif
                    </div>
                </div>
 
                {{-- Actions --}}
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="{{ route('admin.sections.edit', $section) }}" class="btn-rupp-outline" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                    <a href="{{ route('admin.enrollments.index', ['section_id' => $section->id]) }}" class="btn-rupp-primary" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-person-plus"></i> Enroll
                    </a>
                    <a href="{{ route('admin.sections.print-students', $section) }}" target="_blank"
                       class="btn-rupp-outline" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-printer-fill"></i> Print
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px;">
        <i class="bi bi-collection" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500; color:#6b7280;">No sections yet.</div>
        <div style="font-size:13px; color:#9ca3af; margin-top:4px;">Add courses first, then create sections.</div>
        <a href="{{ route('admin.sections.create') }}" class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-plus-lg"></i> Add First Section
        </a>
    </div>
</div>
@endforelse
@endsection