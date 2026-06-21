@extends('layouts.admin')
@section('title', 'Timetable')
@section('page-title', 'Timetable')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Timetable Overview</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Timetable
        </div>
    </div>
</div>

{{-- Summary --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="bi bi-collection-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Sections</div>
            <div class="value">{{ $withTimetable + $withoutTimetable }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="bi bi-calendar-check-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">With Schedule</div>
            <div class="value">{{ $withTimetable }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon" style="background:#fee2e2; color:#dc2626;">
            <i class="bi bi-calendar-x-fill"></i>
        </div>
        <div class="stat-card-info">
            <div class="label">No Schedule Yet</div>
            <div class="value" style="color:#dc2626;">{{ $withoutTimetable }}</div>
        </div>
    </div>
</div>

{{-- Program cards grouped by faculty --}}
@forelse($programs->groupBy(fn($p) => $p->department->faculty->name) as $facultyName => $facultyPrograms)
<div style="margin-bottom:28px;">

    <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
        <div style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px;">
            <i class="bi bi-building"></i> {{ $facultyName }}
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:14px;">
        @foreach($facultyPrograms as $program)
        @php
            $sections     = $program->all_sections;
            $withSched    = $sections->filter(fn($s) => $s->timetables->count() > 0)->count();
            $withoutSched = $sections->filter(fn($s) => $s->timetables->count() === 0)->count();
            $total        = $sections->count();
            $pct          = $total > 0 ? round(($withSched / $total) * 100) : 0;
        @endphp
        <a href="{{ route('admin.timetable.program', $program) }}"
           class="card-rupp"
           style="text-decoration:none; display:block; transition:box-shadow .15s; cursor:pointer;"
           onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
           onmouseout="this.style.boxShadow=''">

            <div style="background:var(--rupp-green); padding:16px 18px; display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                        {{ $program->code }}
                    </div>
                    <div style="color:#fff; font-size:14px; font-weight:600; margin-top:3px; line-height:1.3;">
                        {{ $program->name }}
                    </div>
                    <div style="color:rgba(255,255,255,.55); font-size:11.5px; margin-top:2px;">
                        {{ $program->department->name }}
                    </div>
                </div>
                <div style="background:rgba(201,162,39,0.2); border-radius:8px; padding:8px 12px; text-align:center; flex-shrink:0;">
                    <div style="font-size:20px; font-weight:700; color:var(--rupp-gold); line-height:1;">{{ $total }}</div>
                    <div style="font-size:9px; color:rgba(255,255,255,.5); text-transform:uppercase; margin-top:1px;">sections</div>
                </div>
            </div>

            <div style="padding:14px 18px;">
                {{-- Progress bar --}}
                <div style="margin-bottom:10px;">
                    <div style="display:flex; justify-content:space-between; font-size:11.5px; margin-bottom:5px;">
                        <span style="color:#6b7280;">Schedule coverage</span>
                        <span style="font-weight:600; color:{{ $pct == 100 ? '#166534' : ($pct > 0 ? '#c2410c' : '#dc2626') }};">
                            {{ $pct }}%
                        </span>
                    </div>
                    <div style="height:6px; background:#f3f4f6; border-radius:3px; overflow:hidden;">
                        <div style="height:100%; width:{{ $pct }}%; background:{{ $pct == 100 ? 'var(--rupp-green)' : ($pct > 0 ? '#f59e0b' : '#ef4444') }}; border-radius:3px; transition:width .3s;"></div>
                    </div>
                </div>

                <div style="display:flex; gap:8px; flex-wrap:wrap; font-size:12.5px;">
                    @if($withSched > 0)
                    <span style="color:#166534; background:#dcfce7; border-radius:6px; padding:3px 10px;">
                        <i class="bi bi-check-lg"></i> {{ $withSched }} scheduled
                    </span>
                    @endif
                    @if($withoutSched > 0)
                    <span style="color:#dc2626; background:#fee2e2; border-radius:6px; padding:3px 10px;">
                        <i class="bi bi-x-lg"></i> {{ $withoutSched }} missing
                    </span>
                    @endif
                    @if($total === 0)
                    <span style="color:#9ca3af; font-size:12px;">No sections yet</span>
                    @endif
                </div>

                <div style="margin-top:12px; font-size:12px; color:var(--rupp-green); font-weight:500;">
                    View timetable <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-calendar3" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No programs found.</div>
    </div>
</div>
@endforelse
@endsection