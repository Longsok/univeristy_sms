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

@forelse($departments as $dept)
@php
    $deptSections = $dept->programs->flatMap(fn($p) => $p->courses->flatMap(fn($c) => $c->sections));
    if($deptSections->count() === 0 && $dept->programs->flatMap(fn($p) => $p->courses)->count() === 0) continue;
@endphp

<div style="margin-bottom:32px;">

    {{-- Department header --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
        <div style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:10px; padding:8px 18px; font-size:14px; font-weight:700; display:flex; align-items:center; gap:8px; flex-shrink:0;">
            <i class="bi bi-diagram-3-fill"></i> {{ $dept->name }}
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af;">{{ $deptSections->count() }} section(s)</span>
    </div>

    {{-- Programs --}}
    @foreach($dept->programs->where('is_active', true) as $program)
    @if($program->courses->count() === 0) @continue @endif

    <div style="margin-bottom:20px; padding-left:18px; border-left:3px solid #e5e7eb;">

        {{-- Program label --}}
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <span style="background:#dbeafe; color:#1e40af; border-radius:6px; padding:3px 12px; font-size:12px; font-weight:600;">
                {{ $program->code }}
            </span>
            <span style="font-size:13px; font-weight:600; color:#374151;">{{ $program->name }}</span>
            <div style="height:1px; flex:1; background:#f3f4f6;"></div>
        </div>

        {{-- Courses --}}
        @foreach($program->courses->sortBy('code') as $course)
        <div style="margin-bottom:14px; background:#fff; border:0.5px solid #e5e7eb; border-radius:12px; overflow:hidden;">

            {{-- Course header --}}
            <div style="padding:12px 16px; background:#f9fafb; border-bottom:0.5px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:6px; padding:3px 10px; font-size:11.5px; font-weight:700; letter-spacing:.04em;">
                        {{ $course->code }}
                    </span>
                    <div>
                        <div style="font-size:13.5px; font-weight:600; color:#111827;">{{ $course->name }}</div>
                        <div style="font-size:11px; color:#9ca3af; margin-top:1px;">
                            {{ $course->credit_units }} credits
                            @if($course->semester)
                            · {{ $course->semester->name }} {{ $course->semester->academic_year }}
                            @if($course->semester->isRunning())
                            · <span style="color:#166534; font-weight:600;"><i class="bi bi-circle-fill" style="font-size:6px;"></i> Running</span>
                            @endif
                            @endif
                            @if($course->year_level)
                            · Year {{ $course->year_level }}
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.sections.create', ['course_id' => $course->id]) }}"
                   class="btn-rupp-outline" style="padding:5px 12px; font-size:12px; white-space:nowrap;">
                    <i class="bi bi-plus-lg"></i> Add Section
                </a>
            </div>

            {{-- Sections list --}}
            @if($course->sections->count() > 0)
            <div>
                @foreach($course->sections as $section)
                @php $enrolled = $section->enrollments->where('status','enrolled')->count(); @endphp
                <div style="padding:12px 16px; border-bottom:0.5px solid #f9fafb; display:flex; align-items:center; gap:14px; flex-wrap:wrap;"
                     onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">

                    {{-- Section name --}}
                    <div style="min-width:80px;">
                        <span style="background:#f3f4f6; border-radius:6px; padding:4px 12px; font-size:13px; font-weight:600; color:#374151;">
                            {{ $section->name }}
                        </span>
                    </div>

                    {{-- Teacher --}}
                    <div style="flex:1; font-size:12.5px; color:#6b7280; display:flex; align-items:center; gap:6px;">
                        @if($section->teacher)
                        <i class="bi bi-person-fill" style="color:var(--rupp-green);"></i>
                        {{ $section->teacher->user->name }}
                        @else
                        <span style="color:#d1d5db;"><i class="bi bi-person-fill"></i> No teacher</span>
                        @endif
                    </div>

                    {{-- Enrollment count --}}
                    <div style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:#374151;">
                        <i class="bi bi-people-fill" style="color:var(--rupp-green); font-size:13px;"></i>
                        <span style="font-weight:600;">{{ $enrolled }}</span>
                        <span style="color:#9ca3af;">/ {{ $section->max_students }}</span>
                    </div>

                    {{-- Capacity bar --}}
                    @php $pct = $section->max_students > 0 ? min(100, round(($enrolled / $section->max_students) * 100)) : 0; @endphp
                    <div style="width:80px; height:5px; background:#f3f4f6; border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:{{ $pct }}%; background:{{ $pct >= 90 ? '#ef4444' : 'var(--rupp-green)' }}; border-radius:3px;"></div>
                    </div>

                    {{-- Actions --}}
                    <div style="display:flex; gap:6px; flex-shrink:0;">
                        <a href="{{ route('admin.timetable.index', $section) }}"
                           class="btn-rupp-outline" style="padding:4px 10px; font-size:11.5px;" title="Timetable">
                            <i class="bi bi-calendar3"></i>
                        </a>
                        <a href="{{ route('admin.enrollments.index', ['section_id' => $section->id]) }}"
                           class="btn-rupp-outline" style="padding:4px 10px; font-size:11.5px;" title="Enrollments">
                            <i class="bi bi-people-fill"></i>
                        </a>
                        <a href="{{ route('admin.sections.edit', $section) }}"
                           class="btn-icon edit" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="{{ route('admin.sections.print-students', $section) }}" target="_blank"
                           class="btn-icon" title="Print" style="background:#f0fdf4; color:#166534;">
                            <i class="bi bi-printer-fill"></i>
                        </a>
                        <form action="{{ route('admin.sections.destroy', $section) }}" method="POST"
                              onsubmit="return confirm('Delete section {{ $section->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon delete" title="Delete">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="padding:16px 18px; font-size:12.5px; color:#9ca3af; text-align:center;">
                No sections yet —
                <a href="{{ route('admin.sections.create', ['course_id' => $course->id]) }}"
                   style="color:var(--rupp-green); font-weight:500;">Add first section</a>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-collection" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No sections yet.</div>
        <div style="font-size:13px; margin-top:4px;">Add courses first, then create sections.</div>
        <a href="{{ route('admin.sections.create') }}" class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-plus-lg"></i> Add First Section
        </a>
    </div>
</div>
@endforelse
@endsection