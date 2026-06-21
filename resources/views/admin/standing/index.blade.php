@extends('layouts.admin')
@section('title', 'Academic Standing')
@section('page-title', 'Academic Standing')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Academic Standing</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Academic Standing
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
        <a href="{{ route('admin.standing.program', $program) }}"
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
                    <div style="font-size:20px; font-weight:700; color:var(--rupp-gold); line-height:1;">{{ $program->students_count }}</div>
                    <div style="font-size:9px; color:rgba(255,255,255,.5); text-transform:uppercase; margin-top:1px;">students</div>
                </div>
            </div>

            <div style="padding:14px 18px;">
                @if($program->students_count > 0)
                <div style="font-size:13px; color:#6b7280; margin-bottom:10px;">
                    <i class="bi bi-bar-chart-fill" style="color:var(--rupp-green);"></i>
                    View academic standing by batch
                </div>
                @else
                <div style="font-size:13px; color:#9ca3af;">No active students</div>
                @endif

                <div style="font-size:12px; color:var(--rupp-green); font-weight:500; margin-top:8px;">
                    View Standing <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-mortarboard" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No programs found.</div>
    </div>
</div>
@endforelse
@endsection