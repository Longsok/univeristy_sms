@extends('layouts.admin')
@section('title', '{{ $program->name }} — Courses')
@section('page-title', 'Courses')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $program->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.courses.index') }}">Courses</a> /
            {{ $program->code }}
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.courses.create', ['program_id' => $program->id]) }}" class="btn-rupp-primary">
            <i class="bi bi-plus-lg"></i> Add Course
        </a>
        <a href="{{ route('admin.courses.index') }}" class="btn-rupp-outline">
            <i class="bi bi-arrow-left"></i> All Programs
        </a>
    </div>
</div>

{{-- Program info --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div style="background:var(--rupp-green); padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                {{ $program->code }} — {{ $program->department->name }}
            </div>
            <div style="color:#fff; font-size:17px; font-weight:600; margin-top:3px;">
                {{ $program->name }}
            </div>
            <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:2px;">
                {{ $program->department->faculty->name }}
            </div>
        </div>
        <div style="display:flex; gap:16px; text-align:center;">
            <div style="background:rgba(255,255,255,0.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:22px; font-weight:700; color:#fff;">{{ $courses->count() }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.5);">Total Courses</div>
            </div>
            <div style="background:rgba(255,255,255,0.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:22px; font-weight:700; color:var(--rupp-gold);">{{ $courses->sum('credit_units') }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.5);">Total Credits</div>
            </div>
            <div style="background:rgba(255,255,255,0.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:22px; font-weight:700; color:#fff;">{{ $courses->pluck('year_level')->unique()->count() }}</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.5);">Year Levels</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter by year --}}
<div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; align-items:center;">
    <span style="font-size:13px; color:#6b7280; font-weight:500;">Filter by year:</span>
    <a href="{{ route('admin.courses.program', $program) }}"
       style="padding:5px 14px; border-radius:20px; font-size:12.5px; font-weight:500; text-decoration:none;
              background:{{ !request('year') ? 'var(--rupp-green)' : '#f3f4f6' }};
              color:{{ !request('year') ? '#fff' : '#6b7280' }}; border:1px solid {{ !request('year') ? 'var(--rupp-green)' : '#e5e7eb' }};">
        All Years
    </a>
    @foreach($years as $year)
    <a href="{{ route('admin.courses.program', [$program, 'year' => $year]) }}"
       style="padding:5px 14px; border-radius:20px; font-size:12.5px; font-weight:500; text-decoration:none;
              background:{{ request('year') == $year ? 'var(--rupp-green)' : '#f3f4f6' }};
              color:{{ request('year') == $year ? '#fff' : '#6b7280' }}; border:1px solid {{ request('year') == $year ? 'var(--rupp-green)' : '#e5e7eb' }};">
        Year {{ $year }}
    </a>
    @endforeach
</div>

{{-- Courses grouped by year level --}}
@forelse($coursesByYear as $year => $yearCourses)
<div class="card-rupp" style="margin-bottom:16px;">

    {{-- Year header --}}
    <div style="background:#f9fafb; padding:12px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="background:var(--rupp-green); color:#fff; border-radius:6px; padding:4px 12px; font-size:13px; font-weight:600;">
                Year {{ $year }}
            </span>
            <span style="font-size:12.5px; color:#6b7280;">
                {{ $yearCourses->count() }} {{ Str::plural('course', $yearCourses->count()) }}
                · {{ $yearCourses->sum('credit_units') }} credits
            </span>
        </div>
        <a href="{{ route('admin.courses.create', ['program_id' => $program->id, 'year_level' => $year]) }}"
           class="btn-rupp-outline" style="padding:5px 12px; font-size:12px;">
            <i class="bi bi-plus-lg"></i> Add to Year {{ $year }}
        </a>
    </div>

    {{-- Courses table --}}
    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th style="width:120px;">Code</th>
                    <th>Course Name</th>
                    <th>Semester</th>
                    <th style="text-align:center; width:80px;">Credits</th>
                    <th style="text-align:center; width:120px;">Sections</th>
                    <th style="width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($yearCourses->sortBy('code') as $course)
                <tr>
                    <td>
                        <span class="badge-rupp badge-green" style="font-family:monospace; font-size:12px;">
                            {{ $course->code }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:500;">{{ $course->name }}</div>
                        @if($course->description)
                        <div style="font-size:11.5px; color:#9ca3af; margin-top:2px;">
                            {{ Str::limit($course->description, 60) }}
                        </div>
                        @endif
                    </td>
                    <td style="font-size:12.5px; color:#6b7280;">
                        @if($course->semester)
                        {{ $course->semester->name }} {{ $course->semester->academic_year }}
                        @if($course->semester->is_active)
                        <span class="badge-rupp badge-green" style="font-size:10px; padding:2px 6px; margin-left:4px;">Active</span>
                        @endif
                        @else
                        <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="text-align:center; font-weight:600;">{{ $course->credit_units }}</td>
                    <td style="text-align:center;">
                        @php $sectionCount = $course->sections_count ?? 0; @endphp
                        @if($sectionCount > 0)
                        <a href="{{ route('admin.sections.index', ['course_id' => $course->id]) }}"
                           style="color:var(--rupp-green); font-weight:600; text-decoration:none; font-size:13px;">
                            {{ $sectionCount }} {{ Str::plural('section', $sectionCount) }}
                        </a>
                        @else
                        <span style="color:#9ca3af; font-size:12.5px;">No sections</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn-icon edit" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="{{ route('admin.courses.destroy', $course) }}" method="POST"
                                onsubmit="return confirm('Delete {{ $course->code }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon delete" title="Delete">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-book" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No courses yet for {{ $program->name }}.</div>
        <a href="{{ route('admin.courses.create', ['program_id' => $program->id]) }}"
           class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-plus-lg"></i> Add First Course
        </a>
    </div>
</div>
@endforelse
@endsection