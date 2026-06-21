@extends('layouts.teacher')
@section('title', 'Student List')
@section('page-title', 'Student List')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $section->course->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('teacher.courses.index') }}">Courses</a> /
            {{ $section->name }} / Students
        </div>
    </div>
    <a href="{{ route('teacher.groups.index', $section) }}" class="btn-rupp-outline">
        <i class="bi bi-people-fill"></i> Manage Groups
    </a>
</div>
 
<div class="card-rupp">
    <div class="card-rupp-header">
        <h5><i class="bi bi-people-fill" style="color:var(--rupp-gold)"></i>
            Enrolled Students
            <span class="badge-rupp badge-green" style="margin-left:6px;">{{ $enrollments->total() }}</span>
        </h5>
    </div>
    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Student ID</th>
                    <th>Program</th>
                    <th>Year</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $i => $enrollment)
                <tr>
                    <td style="color:#9ca3af;">{{ $enrollments->firstItem() + $i }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#166534;">
                                {{ strtoupper(substr($enrollment->student->user->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:500;">{{ $enrollment->student->user->name }}</span>
                        </div>
                    </td>
                    <td style="font-family:monospace;font-size:12.5px;color:#6b7280;">{{ $enrollment->student->student_id }}</td>
                    <td style="font-size:12.5px;color:#6b7280;">{{ $enrollment->student->program->code }}</td>
                    <td style="text-align:center;">Year {{ $enrollment->student->year_level }}</td>
                    <td>
                        <span class="badge-rupp {{ $enrollment->status === 'enrolled' ? 'badge-green' : 'badge-gray' }}">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:#9ca3af;">No students enrolled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($enrollments->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #f3f4f6;">{{ $enrollments->links() }}</div>
    @endif
</div>
@endsection
 