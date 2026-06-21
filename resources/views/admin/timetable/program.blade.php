@extends('layouts.admin')
@section('title', '{{ $program->name }} — Timetable')
@section('page-title', 'Timetable')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $program->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.timetable.overview') }}">Timetable</a> /
            {{ $program->code }}
        </div>
    </div>
    <a href="{{ route('admin.timetable.overview') }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> All Programs
    </a>
</div>

{{-- Program info --}}
@php
    $allSections  = $program->all_sections;
    $withSched    = $allSections->filter(fn($s) => $s->timetables->count() > 0)->count();
    $withoutSched = $allSections->filter(fn($s) => $s->timetables->count() === 0)->count();
@endphp
<div class="card-rupp" style="margin-bottom:20px;">
    <div style="background:var(--rupp-green); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                {{ $program->code }} — {{ $program->department->name }}
            </div>
            <div style="color:#fff; font-size:16px; font-weight:600; margin-top:2px;">{{ $program->name }}</div>
            <div style="color:rgba(255,255,255,.6); font-size:12px; margin-top:1px;">{{ $program->department->faculty->name }}</div>
        </div>
        <div style="display:flex; gap:14px; text-align:center;">
            <div style="background:rgba(255,255,255,.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:20px; font-weight:700; color:#fff;">{{ $allSections->count() }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,.5);">Total Sections</div>
            </div>
            <div style="background:rgba(255,255,255,.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:20px; font-weight:700; color:#86efac;">{{ $withSched }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,.5);">Scheduled</div>
            </div>
            @if($withoutSched > 0)
            <div style="background:rgba(220,38,38,.2); border-radius:8px; padding:10px 16px;">
                <div style="font-size:20px; font-weight:700; color:#fca5a5;">{{ $withoutSched }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,.5);">Missing</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Missing schedule warning --}}
@php $missingSections = $allSections->filter(fn($s) => $s->timetables->count() === 0); @endphp
@if($missingSections->count() > 0)
<div class="card-rupp" style="margin-bottom:20px; border:1px solid #fca5a5;">
    <div style="background:#fef2f2; padding:12px 20px; border-bottom:1px solid #fca5a5; display:flex; align-items:center; gap:8px;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626;"></i>
        <span style="font-size:13.5px; font-weight:600; color:#991b1b;">
            {{ $missingSections->count() }} section(s) missing schedule — students cannot see timetable
        </span>
    </div>
    @foreach($missingSections as $section)
    <div style="padding:10px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <span style="font-weight:500; font-size:13px;">{{ $section->course->code }} — {{ $section->name }}</span>
            <span style="font-size:12px; color:#9ca3af; margin-left:8px;">{{ $section->teacher?->user?->name ?? 'No teacher' }}</span>
        </div>
        <a href="{{ route('admin.timetable.index', $section) }}" class="btn-rupp-primary" style="padding:5px 12px; font-size:12px;">
            <i class="bi bi-plus-lg"></i> Add Schedule
        </a>
    </div>
    @endforeach
</div>
@endif

{{-- Sections grouped by year level --}}
@php
    $scheduledByYear = $allSections
        ->filter(fn($s) => $s->timetables->count() > 0)
        ->groupBy(fn($s) => $s->course->year_level ?? 0)
        ->sortKeys();
@endphp

@forelse($scheduledByYear as $year => $yearSections)
<div class="card-rupp" style="margin-bottom:16px;">
    <div style="background:#f9fafb; padding:12px 20px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; gap:10px;">
        <span style="background:var(--rupp-green); color:#fff; border-radius:6px; padding:4px 12px; font-size:13px; font-weight:600;">
            {{ $year > 0 ? 'Year '.$year : 'No Year Set' }}
        </span>
        <span style="font-size:12.5px; color:#6b7280;">{{ $yearSections->count() }} sections</span>
    </div>

    @foreach($yearSections->sortBy('name') as $section)
    <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
            <div>
                <div style="font-size:14px; font-weight:600; color:var(--rupp-green);">
                    {{ $section->course->code }} — {{ $section->course->name }}
                    <span class="badge-rupp badge-blue" style="margin-left:8px; font-size:10px;">{{ $section->name }}</span>
                </div>
                <div style="font-size:12px; color:#9ca3af; margin-top:2px;">
                    {{ $section->teacher?->user?->name ?? 'No teacher' }}
                    · {{ $section->enrollments->where('status','enrolled')->count() }} students
                </div>
            </div>
            <a href="{{ route('admin.timetable.index', $section) }}" class="btn-rupp-outline" style="padding:5px 12px; font-size:12px;">
                <i class="bi bi-pencil-fill"></i> Manage
            </a>
        </div>

        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            @foreach($section->timetables->sortBy(fn($t) => array_search($t->day_of_week, $days)) as $tt)
            <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:6px 12px; font-size:12px;">
                <span style="font-weight:600; color:var(--rupp-green);">{{ substr($tt->day_of_week, 0, 3) }}</span>
                <span style="color:#374151; margin-left:6px;">{{ substr($tt->start_time,0,5) }}–{{ substr($tt->end_time,0,5) }}</span>
                @if($tt->room)
                <span style="color:#9ca3af; margin-left:4px;">· Rm {{ $tt->room }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:40px; color:#9ca3af;">
        <i class="bi bi-calendar-x" style="font-size:36px; display:block; margin-bottom:10px;"></i>
        <div style="font-size:14px; font-weight:500;">No scheduled sections yet for {{ $program->name }}.</div>
        @if($missingSections->count() > 0)
        <div style="font-size:12.5px; margin-top:4px;">Add schedules to the sections listed above.</div>
        @endif
    </div>
</div>
@endforelse
@endsection