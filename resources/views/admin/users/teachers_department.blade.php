@extends('layouts.admin')
@section('title', '{{ $department->name }} — Teachers')
@section('page-title', 'Teachers')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $department->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.teachers.index') }}">Teachers</a> /
            {{ $department->name }}
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.users.create') }}" class="btn-rupp-primary">
            <i class="bi bi-person-plus-fill"></i> Add Teacher
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="btn-rupp-outline">
            <i class="bi bi-arrow-left"></i> All Teachers
        </a>
    </div>
</div>

{{-- Department info --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div style="background:var(--rupp-green); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                {{ $department->faculty->name }}
            </div>
            <div style="color:#fff; font-size:16px; font-weight:600; margin-top:2px;">{{ $department->name }}</div>
            @if($department->head_name)
            <div style="color:rgba(255,255,255,.6); font-size:12px; margin-top:1px;">
                <i class="bi bi-person-fill"></i> Head: {{ $department->head_name }}
            </div>
            @endif
        </div>
        <div style="text-align:center; background:rgba(255,255,255,.1); border-radius:8px; padding:10px 20px;">
            <div style="font-size:24px; font-weight:700; color:#fff;">{{ $teachers->count() }}</div>
            <div style="font-size:10px; color:rgba(255,255,255,.5);">Teachers</div>
        </div>
    </div>
</div>

{{-- Search --}}
<div class="card-rupp" style="margin-bottom:16px;">
    <div class="card-rupp-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex; gap:10px; align-items:center;">
            <div style="position:relative; flex:1;">
                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px;"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name or email..."
                    style="width:100%; padding:7px 12px 7px 32px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; outline:none;">
            </div>
            <button type="submit" class="btn-rupp-primary" style="padding:7px 14px;">
                <i class="bi bi-search"></i> Search
            </button>
            @if(request('search'))
            <a href="{{ route('admin.teachers.department', $department) }}" class="btn-rupp-outline" style="padding:6px 12px;">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Teacher cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:14px;">
    @forelse($teachers as $teacher)
    @php
        $totalSections  = $teacher->sections->count();
        $inSession      = $teacher->sections->filter(fn($s) => $s->course?->semester?->isRunning())->count();
        $totalStudents  = $teacher->sections->flatMap(fn($s) => $s->enrollments->where('status','enrolled'))->count();
    @endphp

    <div class="card-rupp">
        {{-- Teacher header --}}
        <div style="padding:16px 18px; display:flex; align-items:flex-start; gap:14px; border-bottom:1px solid #f3f4f6;">
            <div style="width:48px; height:48px; border-radius:50%; background:#fdf3d7; display:flex; align-items:center; justify-content:center; font-size:18px; font-weight:700; color:#92680a; flex-shrink:0;">
                @if($teacher->user->photo)
                    <img src="{{ $teacher->user->photo_url }}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                @else
                    {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                @endif
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:15px; font-weight:700; color:#111827;">{{ $teacher->user->name }}</div>
                @if($teacher->user->name_kh)
                <div style="font-size:12px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $teacher->user->name_kh }}</div>
                @endif
                <div style="font-size:12px; color:#9ca3af; margin-top:2px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span>{{ $teacher->user->email }}</span>
                    @if($teacher->employee_id)
                    <span style="font-family:monospace; color:#6b7280;">· {{ $teacher->employee_id }}</span>
                    @endif
                </div>
                @if($teacher->specialization)
                <div style="font-size:11.5px; color:#6b7280; margin-top:4px;">
                    <i class="bi bi-award" style="color:var(--rupp-gold);"></i>
                    {{ $teacher->specialization }}
                </div>
                @endif
            </div>
            <span class="badge-rupp {{ $teacher->user->is_active ? 'badge-green' : 'badge-red' }}" style="font-size:10px; flex-shrink:0;">
                {{ $teacher->user->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        {{-- Stats --}}
        <div style="padding:12px 18px; display:grid; grid-template-columns:repeat(3,1fr); gap:8px; border-bottom:1px solid #f3f4f6;">
            <div style="text-align:center; background:#f9fafb; border-radius:8px; padding:8px 4px;">
                <div style="font-size:18px; font-weight:700; color:#374151;">{{ $totalSections }}</div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase;">Sections</div>
            </div>
            <div style="text-align:center; background:{{ $inSession > 0 ? '#f0fdf4' : '#f9fafb' }}; border-radius:8px; padding:8px 4px;">
                <div style="font-size:18px; font-weight:700; color:{{ $inSession > 0 ? '#166534' : '#374151' }};">{{ $inSession }}</div>
                <div style="font-size:10px; color:{{ $inSession > 0 ? '#16a34a' : '#9ca3af' }}; text-transform:uppercase;">In Session</div>
            </div>
            <div style="text-align:center; background:#f9fafb; border-radius:8px; padding:8px 4px;">
                <div style="font-size:18px; font-weight:700; color:#374151;">{{ $totalStudents }}</div>
                <div style="font-size:10px; color:#9ca3af; text-transform:uppercase;">Students</div>
            </div>
        </div>

        {{-- Current sections --}}
        @if($teacher->sections->count() > 0)
        <div style="padding:10px 18px;">
            <div style="font-size:11px; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Sections</div>
            @foreach($teacher->sections->take(3) as $section)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px solid #f9fafb; font-size:12px;">
                <span style="color:#374151;">{{ $section->course->code }} — {{ $section->name }}</span>
                @if($section->course->semester?->isRunning())
                <span style="background:#dcfce7; color:#166534; border-radius:10px; padding:1px 8px; font-size:10px; font-weight:600;">
                    <i class="bi bi-circle-fill" style="font-size:5px;"></i> Running
                </span>
                @endif
            </div>
            @endforeach
            @if($teacher->sections->count() > 3)
            <div style="font-size:11.5px; color:#9ca3af; padding-top:4px;">
                +{{ $teacher->sections->count() - 3 }} more sections
            </div>
            @endif
        </div>
        @endif

        {{-- Actions --}}
        <div style="padding:10px 18px; display:flex; gap:8px;">
            <a href="{{ route('admin.users.edit', $teacher->user) }}"
               class="btn-rupp-outline" style="flex:1; justify-content:center; font-size:12px; padding:6px;">
                <i class="bi bi-pencil-fill"></i> Edit
            </a>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;" class="card-rupp">
        <div class="card-rupp-body" style="text-align:center; padding:40px; color:#9ca3af;">
            <i class="bi bi-person-workspace" style="font-size:36px; display:block; margin-bottom:10px;"></i>
            <div style="font-size:14px; font-weight:500;">No teachers in {{ $department->name }} yet.</div>
        </div>
    </div>
    @endforelse
</div>
@endsection