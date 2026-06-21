@extends('layouts.admin')
@section('title', 'Departments')
@section('page-title', 'Departments')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Departments</h1>
        <div class="breadcrumb-text"><a href="{{ route('admin.dashboard') }}">Dashboard</a> / Departments</div>
    </div>
    <a href="{{ route('admin.departments.create') }}" class="btn-rupp-primary">
        <i class="bi bi-plus-lg"></i> Add Department
    </a>
</div>
 
@if(session('success'))
    <div class="alert-success-rupp" style="margin-bottom:16px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif
 
{{-- Filter by faculty --}}
<div class="card-rupp" style="margin-bottom:16px;">
    <div class="card-rupp-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <label style="font-size:13px; font-weight:500; color:#374151;">Filter by Faculty:</label>
            <select name="faculty_id" class="form-select-rupp" style="width:auto; min-width:220px;">
                <option value="">All Faculties</option>
                @foreach(\App\Models\Faculty::all() as $faculty)
                    <option value="{{ $faculty->id }}" {{ request('faculty_id') == $faculty->id ? 'selected' : '' }}>
                        {{ $faculty->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-rupp-primary" style="padding:8px 14px;">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if(request('faculty_id'))
                <a href="{{ route('admin.departments.index') }}" class="btn-rupp-outline" style="padding:7px 14px;">Clear</a>
            @endif
        </form>
    </div>
</div>
 
{{-- Group departments by faculty --}}
@foreach($departments->groupBy(fn($d) => $d->faculty->name) as $facultyName => $facultyDepts)
<div style="margin-bottom:28px;">
 
    {{-- Faculty label --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <div style="background:var(--rupp-green); color:#fff; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-building"></i> {{ $facultyName }}
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af;">{{ $facultyDepts->count() }} {{ Str::plural('department', $facultyDepts->count()) }}</span>
    </div>
 
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:14px;">
        @foreach($facultyDepts as $dept)
        <div class="card-rupp">
            {{-- Header --}}
            <div style="background:#f9fafb; border-bottom:3px solid var(--rupp-green); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="font-size:11px; font-weight:600; color:var(--rupp-gold); letter-spacing:.06em; text-transform:uppercase; margin-bottom:3px;">
                        {{ $dept->code }}
                    </div>
                    <div style="font-size:14px; font-weight:600; color:var(--rupp-green); line-height:1.3;">
                        {{ $dept->name }}
                    </div>
                </div>
                <span class="badge-rupp {{ $dept->is_active ? 'badge-green' : 'badge-red' }}" style="font-size:10px;">
                    {{ $dept->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
 
            {{-- Body --}}
            <div class="card-rupp-body" style="padding:14px 16px;">
                {{-- Faculty --}}
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; margin-bottom:10px;">
                    <i class="bi bi-building" style="color:var(--rupp-green);"></i>
                    {{ $facultyName }}
                </div>
 
                {{-- Head --}}
                <div style="display:flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; margin-bottom:12px;">
                    <i class="bi bi-person-fill" style="color:var(--rupp-gold);"></i>
                    {{ $dept->head_name ?? 'No head assigned' }}
                </div>
 
                {{-- Stats --}}
                <div style="display:flex; gap:12px; margin-bottom:12px;">
                    <div style="background:#f9fafb; border-radius:8px; padding:8px 12px; text-align:center; flex:1;">
                        <div style="font-size:18px; font-weight:700; color:var(--rupp-green);">{{ $dept->teachers_count }}</div>
                        <div style="font-size:10px; color:#9ca3af;">Teachers</div>
                    </div>
                    <div style="background:#f9fafb; border-radius:8px; padding:8px 12px; text-align:center; flex:1;">
                        <div style="font-size:18px; font-weight:700; color:var(--rupp-green);">{{ $dept->programs_count }}</div>
                        <div style="font-size:10px; color:#9ca3af;">Programs</div>
                    </div>
                </div>
 
                {{-- Programs preview --}}
                @if($dept->programs->count())
                <div style="margin-bottom:12px;">
                    <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:5px;">Programs</div>
                    <div style="display:flex; flex-wrap:wrap; gap:5px;">
                        @foreach($dept->programs as $prog)
                        <span class="badge-rupp badge-gold" style="font-size:10.5px;">{{ $prog->code }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
 
                {{-- Actions --}}
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('admin.departments.edit', $dept) }}" class="btn-rupp-outline" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                    <a href="{{ route('admin.programs.index', ['department_id' => $dept->id]) }}" class="btn-rupp-primary" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-mortarboard"></i> Programs
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
 
@if($departments->isEmpty())
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px;">
        <i class="bi bi-diagram-3" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500; color:#6b7280;">No departments yet.</div>
        <div style="font-size:13px; color:#9ca3af; margin-top:4px;">Add faculties first, then add departments.</div>
        <a href="{{ route('admin.departments.create') }}" class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-plus-lg"></i> Add First Department
        </a>
    </div>
</div>
@endif
@endsection