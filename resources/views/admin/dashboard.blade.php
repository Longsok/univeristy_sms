@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Admin Dashboard</h1>
        <div class="breadcrumb-text">Overview of the entire university system</div>
    </div>
</div>

{{-- Stat cards — all clickable --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:16px; margin-bottom:28px;">

    <a href="{{ route('admin.users.index', ['role' => 'student']) }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon green"><i class="bi bi-people-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Students</div>
            <div class="value">{{ $totalStudents }}</div>
        </div>
    </a>

    <a href="{{ route('admin.users.index', ['role' => 'teacher']) }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon gold"><i class="bi bi-person-workspace"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Teachers</div>
            <div class="value">{{ $totalTeachers }}</div>
        </div>
    </a>

    <a href="{{ route('admin.courses.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon blue"><i class="bi bi-book-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Total Courses</div>
            <div class="value">{{ $totalCourses }}</div>
        </div>
    </a>

    <a href="{{ route('admin.faculties.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon green"><i class="bi bi-building"></i></div>
        <div class="stat-card-info">
            <div class="label">Faculties</div>
            <div class="value">{{ $totalFaculties }}</div>
        </div>
    </a>

    <a href="{{ route('admin.departments.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon gold"><i class="bi bi-diagram-3-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Departments</div>
            <div class="value">{{ $totalDepartments }}</div>
        </div>
    </a>

    <a href="{{ route('admin.programs.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon blue"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Programs</div>
            <div class="value">{{ $totalPrograms }}</div>
        </div>
    </a>

</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px;" class="responsive-grid-2">
    {{-- Recent users --}}
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5><i class="bi bi-person-plus-fill" style="color:var(--rupp-gold)"></i> Recently Added Users</h5>
            <a href="{{ route('admin.users.index') }}" class="btn-rupp-outline" style="font-size:12px; padding:5px 12px;">
                View All
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="table-rupp">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $user)
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:{{ $user->isStudent() ? '#dcfce7' : ($user->isTeacher() ? '#fdf3d7' : '#dbeafe') }};display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;color:{{ $user->isStudent() ? '#166534' : ($user->isTeacher() ? '#92680a' : '#1e40af') }};">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span style="font-weight:500;">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td style="color:#6b7280;">{{ $user->email }}</td>
                        <td>
                            <span class="badge-rupp {{ $user->isAdmin() ? 'badge-blue' : ($user->isTeacher() ? 'badge-gold' : 'badge-green') }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-rupp {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Announcements + Active semester --}}
    <div style="display:flex; flex-direction:column; gap:16px;">
        @if($activeSemester)
        <div class="card-rupp">
            <div style="background:var(--rupp-green); padding:16px 20px; display:flex; align-items:center; gap:10px;">
                <i class="bi bi-calendar-check-fill" style="color:var(--rupp-gold); font-size:18px;"></i>
                <div>
                    <div style="color:#fff; font-weight:600; font-size:13.5px;">Active Semester</div>
                    <div style="color:rgba(255,255,255,0.65); font-size:11px;">Currently running</div>
                </div>
            </div>
            <div class="card-rupp-body">
                <div style="font-size:18px; font-weight:600; color:var(--rupp-green);">{{ $activeSemester->name }}</div>
                <div style="font-size:13px; color:#6b7280; margin-top:2px;">Academic Year {{ $activeSemester->academic_year }}</div>
                <div style="font-size:12px; color:#9ca3af; margin-top:10px; display:flex; gap:16px;">
                    <span><i class="bi bi-calendar3"></i> {{ $activeSemester->start_date->format('d M Y') }}</span>
                    <span>→</span>
                    <span>{{ $activeSemester->end_date->format('d M Y') }}</span>
                </div>
            </div>
        </div>
        @endif

        <div class="card-rupp" style="flex:1;">
            <div class="card-rupp-header">
                <h5><i class="bi bi-megaphone-fill" style="color:var(--rupp-gold)"></i> Announcements</h5>
            </div>
            <div class="card-rupp-body" style="padding:0;">
                @forelse($announcements as $ann)
                <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;">
                    <div style="font-size:13px; font-weight:600; color:#111827; margin-bottom:3px;">{{ $ann->title }}</div>
                    <div style="font-size:11.5px; color:#9ca3af;">
                        <span class="badge-rupp {{ $ann->target_role === 'all' ? 'badge-blue' : ($ann->target_role === 'student' ? 'badge-green' : 'badge-gold') }}" style="font-size:10px; padding:2px 7px;">
                            {{ ucfirst($ann->target_role) }}
                        </span>
                        &nbsp;{{ $ann->published_at->diffForHumans() }}
                    </div>
                </div>
                @empty
                <div style="padding:20px; text-align:center; color:#9ca3af; font-size:13px;">No announcements yet</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection