@extends('layouts.admin')
@section('title', 'Programs')
@section('page-title', 'Programs')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Programs</h1>
        <div class="breadcrumb-text"><a href="{{ route('admin.dashboard') }}">Dashboard</a> / Programs</div>
    </div>
    <a href="{{ route('admin.programs.create') }}" class="btn-rupp-primary">
        <i class="bi bi-plus-lg"></i> Add Program
    </a>
</div>
 
@if(session('success'))
    <div class="alert-success-rupp" style="margin-bottom:16px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif
 
{{-- Filter by department --}}
<div class="card-rupp" style="margin-bottom:16px;">
    <div class="card-rupp-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <label style="font-size:13px; font-weight:500; color:#374151;">Filter by Department:</label>
            <select name="department_id" class="form-select-rupp" style="width:auto; min-width:220px;">
                <option value="">All Departments</option>
                @foreach(\App\Models\Department::with('faculty')->get() as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }} ({{ $dept->faculty->name }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-rupp-primary" style="padding:8px 14px;">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if(request('department_id'))
                <a href="{{ route('admin.programs.index') }}" class="btn-rupp-outline" style="padding:7px 14px;">Clear</a>
            @endif
        </form>
    </div>
</div>
 
{{-- Programs grouped by Department --}}
@foreach($programs->groupBy(fn($p) => $p->department->name) as $deptName => $deptPrograms)
<div style="margin-bottom:28px;">
 
    {{-- Department + Faculty label --}}
    @php $firstDept = $deptPrograms->first()->department; @endphp
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <div style="display:flex; align-items:center; gap:0;">
            <div style="background:var(--rupp-green); color:#fff; border-radius:8px 0 0 8px; padding:6px 14px; font-size:12px; font-weight:600;">
                <i class="bi bi-building"></i> {{ $firstDept->faculty->name }}
            </div>
            <div style="background:var(--rupp-gold); color:var(--rupp-green); border-radius:0 8px 8px 0; padding:6px 14px; font-size:12px; font-weight:600;">
                <i class="bi bi-diagram-3"></i> {{ $deptName }}
            </div>
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af;">{{ $deptPrograms->count() }} {{ Str::plural('program', $deptPrograms->count()) }}</span>
    </div>
 
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:14px;">
        @foreach($deptPrograms as $program)
        <div class="card-rupp">
            {{-- Header --}}
            <div style="background:var(--rupp-green); padding:14px 16px; display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; margin-bottom:3px;">
                        {{ $program->code }}
                    </div>
                    <div style="color:#fff; font-size:14px; font-weight:600; line-height:1.3;">
                        {{ $program->name }}
                    </div>
                </div>
                <span class="badge-rupp {{ $program->is_active ? 'badge-green' : 'badge-red' }}" style="font-size:10px; white-space:nowrap; margin-top:2px;">
                    {{ $program->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
 
            {{-- Body --}}
            <div class="card-rupp-body" style="padding:14px 16px;">
 
                {{-- Department + Faculty --}}
                <div style="font-size:12px; color:#6b7280; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                    <i class="bi bi-diagram-3" style="color:var(--rupp-green);"></i>
                    {{ $deptName }}
                </div>
                <div style="font-size:11px; color:#9ca3af; margin-bottom:12px; padding-left:18px;">
                    {{ $firstDept->faculty->name }}
                </div>
 
                {{-- Info row --}}
                <div style="display:flex; gap:10px; margin-bottom:12px;">
                    <div style="background:#f9fafb; border-radius:8px; padding:8px 10px; text-align:center; flex:1;">
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:2px;">Level</div>
                        <div style="font-size:12px; font-weight:600; color:#374151;">{{ ucfirst($program->degree_level) }}</div>
                    </div>
                    <div style="background:#f9fafb; border-radius:8px; padding:8px 10px; text-align:center; flex:1;">
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:2px;">Credits</div>
                        <div style="font-size:12px; font-weight:600; color:#374151;">{{ $program->total_credits }}</div>
                    </div>
                    <div style="background:#f9fafb; border-radius:8px; padding:8px 10px; text-align:center; flex:1;">
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:2px;">Students</div>
                        <div style="font-size:12px; font-weight:600; color:var(--rupp-green);">{{ $program->students_count }}</div>
                    </div>
                </div>
 
                {{-- Actions --}}
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('admin.programs.edit', $program) }}" class="btn-rupp-outline" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                    <a href="{{ route('admin.courses.index', ['program_id' => $program->id]) }}" class="btn-rupp-primary" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-book"></i> Courses
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach
 
@if($programs->isEmpty())
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px;">
        <i class="bi bi-mortarboard" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500; color:#6b7280;">No programs yet.</div>
        <div style="font-size:13px; color:#9ca3af; margin-top:4px;">Add departments first, then add programs.</div>
        <a href="{{ route('admin.programs.create') }}" class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-plus-lg"></i> Add First Program
        </a>
    </div>
</div>
@endif
@endsection