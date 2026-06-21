@extends('layouts.admin')
@section('title', 'Faculties')
@section('page-title', 'Faculties')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Faculties</h1>
        <div class="breadcrumb-text"><a href="{{ route('admin.dashboard') }}">Dashboard</a> / Faculties</div>
    </div>
    <a href="{{ route('admin.faculties.create') }}" class="btn-rupp-primary">
        <i class="bi bi-plus-lg"></i> Add Faculty
    </a>
</div>
 
@if(session('success'))
    <div class="alert-success-rupp" style="margin-bottom:16px;">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
@endif
 
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px;">
    @forelse($faculties as $faculty)
    <div class="card-rupp">
        {{-- Header --}}
        <div style="background:var(--rupp-green); padding:16px 20px; display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase; margin-bottom:3px;">
                    {{ $faculty->code }}
                </div>
                <div style="color:#fff; font-size:15px; font-weight:600; line-height:1.3;">
                    {{ $faculty->name }}
                </div>
            </div>
            <span class="badge-rupp {{ $faculty->is_active ? 'badge-green' : 'badge-red' }}" style="font-size:10px; white-space:nowrap; margin-top:2px;">
                {{ $faculty->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
 
        {{-- Body --}}
        <div class="card-rupp-body">
            {{-- Dean --}}
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px; font-size:13px; color:#6b7280;">
                <i class="bi bi-person-fill" style="color:var(--rupp-gold);"></i>
                {{ $faculty->dean_name ?? 'No dean assigned' }}
            </div>
 
            {{-- Stats --}}
            <div style="display:flex; gap:16px; padding:10px 0; border-top:1px solid #f3f4f6; border-bottom:1px solid #f3f4f6; margin-bottom:12px;">
                <div style="text-align:center; flex:1;">
                    <div style="font-size:20px; font-weight:700; color:var(--rupp-green);">{{ $faculty->departments_count }}</div>
                    <div style="font-size:11px; color:#9ca3af;">Departments</div>
                </div>
                <div style="width:1px; background:#f3f4f6;"></div>
                <div style="text-align:center; flex:1;">
                    <div style="font-size:20px; font-weight:700; color:var(--rupp-green);">{{ $faculty->departments->flatMap->programs->count() }}</div>
                    <div style="font-size:11px; color:#9ca3af;">Programs</div>
                </div>
                <div style="width:1px; background:#f3f4f6;"></div>
                <div style="text-align:center; flex:1;">
                    <div style="font-size:20px; font-weight:700; color:var(--rupp-green);">{{ $faculty->departments->flatMap->teachers->count() }}</div>
                    <div style="font-size:11px; color:#9ca3af;">Teachers</div>
                </div>
            </div>
 
            {{-- Departments preview --}}
            @if($faculty->departments->count())
            <div style="margin-bottom:12px;">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Departments</div>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    @foreach($faculty->departments as $dept)
                    <span class="badge-rupp badge-blue" style="font-size:11px;">{{ $dept->code }}</span>
                    @endforeach
                </div>
            </div>
            @endif
 
            {{-- Actions --}}
            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.faculties.edit', $faculty) }}" class="btn-rupp-outline" style="padding:6px 14px; font-size:12px; flex:1; justify-content:center;">
                    <i class="bi bi-pencil-fill"></i> Edit
                </a>
                <a href="{{ route('admin.departments.index', ['faculty_id' => $faculty->id]) }}" class="btn-rupp-primary" style="padding:6px 14px; font-size:12px; flex:1; justify-content:center;">
                    <i class="bi bi-diagram-3"></i> Departments
                </a>
            </div>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;">
        <div class="card-rupp">
            <div class="card-rupp-body" style="text-align:center; padding:48px;">
                <i class="bi bi-building" style="font-size:40px; color:#d1d5db; display:block; margin-bottom:12px;"></i>
                <div style="font-size:15px; font-weight:500; color:#6b7280;">No faculties yet.</div>
                <a href="{{ route('admin.faculties.create') }}" class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
                    <i class="bi bi-plus-lg"></i> Add First Faculty
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection