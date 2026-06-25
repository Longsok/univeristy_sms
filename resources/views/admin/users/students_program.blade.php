@extends('layouts.admin')
@section('title', '{{ $program->name }} — Students')
@section('page-title', 'Students')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $program->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.students.index') }}">Students</a> /
            {{ $program->code }}
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.users.create') }}?role=student&program_id={{ $program->id }}" class="btn-rupp-primary">
            <i class="bi bi-person-plus-fill"></i> Add Student
        </a>
        <a href="{{ route('admin.students.index') }}" class="btn-rupp-outline">
            <i class="bi bi-arrow-left"></i> All Programs
        </a>
    </div>
</div>

{{-- Program info --}}
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
                <div style="font-size:20px; font-weight:700; color:#fff;">{{ $students->count() }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,.5);">Total</div>
            </div>
            <div style="background:rgba(255,255,255,.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:20px; font-weight:700; color:#86efac;">{{ $students->where('status','active')->count() }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,.5);">Active</div>
            </div>
            <div style="background:rgba(255,255,255,.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:20px; font-weight:700; color:var(--rupp-gold);">{{ $byBatch->count() }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,.5);">Batches</div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-rupp" style="margin-bottom:16px;">
    <div class="card-rupp-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <div style="position:relative; flex:1; min-width:180px;">
                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:13px;"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name..."
                    style="width:100%; padding:7px 12px 7px 32px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; outline:none;">
            </div>
            @if($batches->count() > 0)
            <select name="batch" class="form-select-rupp" style="min-width:130px;" onchange="this.form.submit()">
                <option value="">All Batches</option>
                @foreach($batches as $b)
                <option value="{{ $b }}" {{ request('batch') == $b ? 'selected' : '' }}>Batch {{ $b }}</option>
                @endforeach
            </select>
            @endif
            <select name="year_level" class="form-select-rupp" style="min-width:120px;" onchange="this.form.submit()">
                <option value="">All Years</option>
                @foreach($years as $y)
                <option value="{{ $y }}" {{ request('year_level') == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select-rupp" style="min-width:120px;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Graduated</option>
                <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn-rupp-primary" style="padding:7px 14px;">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if(request('batch') || request('year_level') || request('status') || request('search'))
            <a href="{{ route('admin.students.program', $program) }}" class="btn-rupp-outline" style="padding:7px 12px;">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Students grouped by batch --}}
@forelse($byBatch as $batch => $batchStudents)
@php $batchLabel = $batch > 0 ? "Batch {$batch}" : 'No Batch Assigned'; @endphp

<div class="card-rupp" style="margin-bottom:14px;">

    {{-- Batch header - clickable --}}
    <div onclick="toggleBatch('batch-{{ $batch }}')" style="cursor:pointer; user-select:none;">
        <div style="padding:14px 20px; background:#f9fafb; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:8px; padding:5px 14px; font-size:13px; font-weight:700;">
                    {{ $batchLabel }}
                </div>
                <div style="font-size:13px; color:#6b7280;">
                    {{ $batchStudents->count() }} students
                </div>
                {{-- Year level breakdown --}}
                @foreach($batchStudents->groupBy('year_level')->sortKeys() as $yr => $yrStudents)
                <span style="background:#f0fdf4; border:1px solid #86efac; border-radius:20px; padding:2px 10px; font-size:11px; color:#166534;">
                    Year {{ $yr }}: {{ $yrStudents->count() }}
                </span>
                @endforeach
            </div>
            <i class="bi bi-chevron-down batch-icon-{{ $batch }}" style="font-size:16px; color:#9ca3af; transition:transform .2s;"></i>
        </div>
    </div>

    {{-- Expandable student table --}}
    <div id="batch-{{ $batch }}">
        <div style="overflow-x:auto;">
            <table class="table-rupp">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th style="text-align:center;">Year</th>
                        <th style="text-align:center;">Batch</th>
                        <th>Class Group</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batchStudents->sortBy('student_id')->values() as $i => $student)
                    <tr>
                        <td style="color:#9ca3af; font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:32px; height:32px; border-radius:50%; background:#dcfce7; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; color:#166534; flex-shrink:0;">
                                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:500; font-size:13px;">{{ $student->user->name }}</div>
                                    @if($student->user->name_kh)
                                    <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $student->user->name_kh }}</div>
                                    @endif
                                    <div style="font-size:11px; color:#9ca3af;">{{ $student->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace; font-size:12.5px; color:#374151;">
                            {{ $student->student_id }}
                        </td>
                        <td style="text-align:center;">
                            <span class="badge-rupp badge-blue">Year {{ $student->year_level }}</span>
                        </td>
                        <td style="text-align:center;">
                            @if($student->batch)
                            <span style="background:#dbeafe; color:#1e40af; border-radius:20px; padding:3px 10px; font-size:11px; font-weight:600;">
                                Batch {{ $student->batch }}
                            </span>
                            @else
                            <span style="color:#d1d5db; font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($student->classGroup)
                            <span class="badge-rupp badge-green">{{ $student->classGroup->name }}</span>
                            @else
                            <span style="color:#9ca3af; font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge-rupp {{ match($student->status) {
                                'active'    => 'badge-green',
                                'graduated' => 'badge-gold',
                                default     => 'badge-gray'
                            } }}">
                                <i class="bi bi-circle-fill" style="font-size:6px;"></i>
                                {{ ucfirst($student->status) }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <a href="{{ route('admin.users.edit', $student->user) }}"
                                   class="btn-icon edit" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:40px; color:#9ca3af;">
        <i class="bi bi-people" style="font-size:36px; display:block; margin-bottom:10px;"></i>
        <div style="font-size:14px; font-weight:500;">No students found for {{ $program->name }}.</div>
        <a href="{{ route('admin.users.create') }}?role=student&program_id={{ $program->id }}"
           class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-person-plus-fill"></i> Add First Student
        </a>
    </div>
</div>
@endforelse
@endsection

@push('scripts')
<script>
function toggleBatch(id) {
    const panel = document.getElementById(id);
    const batch = id.replace('batch-', '');
    const icon  = document.querySelector('.batch-icon-' + batch);
    if (!panel) return;
    const open = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : 'block';
    if (icon) icon.style.transform = open ? '' : 'rotate(180deg)';
}
</script>
@endpush