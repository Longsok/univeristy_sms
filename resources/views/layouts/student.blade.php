<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student') — RUPP SMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Hanuman:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --rupp-green:       #1e4d2b;
            --rupp-green-dark:  #163820;
            --rupp-green-light: #2a6b3c;
            --rupp-gold:        #c9a227;
            --rupp-gold-light:  #e0b84a;
            --rupp-gold-pale:   #fdf3d7;
            --sidebar-width:    250px;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; }

        .sidebar {
            position: fixed; top: 0; left: 0;
            width: var(--sidebar-width); height: 100vh;
            background: var(--rupp-green-dark);
            display: flex; flex-direction: column;
            z-index: 100; overflow-y: auto;
        }
        .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 20px 20px 16px; border-bottom: 1px solid rgba(201,162,39,0.25); text-decoration: none; }
        .sidebar-brand img { width: 42px; height: 42px; object-fit: contain; }
        .sidebar-brand-text .title { font-family: 'Hanuman', serif; font-size: 13px; font-weight: 700; color: var(--rupp-gold); display: block; }
        .sidebar-brand-text .subtitle { font-size: 10px; color: rgba(255,255,255,0.5); display: block; }
        .sidebar-role-badge { margin: 12px 16px; background: rgba(201,162,39,0.15); border: 1px solid rgba(201,162,39,0.3); border-radius: 6px; padding: 6px 12px; font-size: 11px; font-weight: 600; color: var(--rupp-gold); letter-spacing: .05em; text-transform: uppercase; }
        .sidebar-section-label { font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,0.35); padding: 16px 20px 6px; }
        .sidebar-nav { list-style: none; padding: 0 10px; margin: 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; color: rgba(255,255,255,0.75); text-decoration: none; font-size: 13.5px; font-weight: 500; transition: background .15s, color .15s; margin-bottom: 2px; }
        .sidebar-nav a i { font-size: 16px; flex-shrink: 0; }
        .sidebar-nav a:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar-nav a.active { background: var(--rupp-gold); color: var(--rupp-green-dark); font-weight: 600; }
        .sidebar-footer { margin-top: auto; padding: 16px; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 8px; background: rgba(255,255,255,0.06); }
        .sidebar-user-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--rupp-gold); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; color: var(--rupp-green-dark); flex-shrink: 0; overflow: hidden; }
        .sidebar-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .sidebar-user-info .name { font-size: 12.5px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-info .role { font-size: 11px; color: rgba(255,255,255,0.45); }
        .sidebar-logout { background: none; border: none; padding: 4px; color: rgba(255,255,255,0.4); cursor: pointer; font-size: 16px; }
        .sidebar-logout:hover { color: #ef4444; }
        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 0 28px; height: 60px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 16px; font-weight: 600; color: var(--rupp-green); }
        .page-content { padding: 28px; flex: 1; }
        .card-rupp { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
        .card-rupp-header { padding: 16px 20px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; justify-content: space-between; }
        .card-rupp-header h5 { font-size: 14px; font-weight: 600; color: var(--rupp-green); margin: 0; display: flex; align-items: center; gap: 8px; }
        .card-rupp-body { padding: 20px; }
        .table-rupp { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .table-rupp thead th { background: #f9fafb; padding: 10px 14px; font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table-rupp tbody td { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: middle; }
        .table-rupp tbody tr:last-child td { border-bottom: none; }
        .table-rupp tbody tr:hover td { background: #f9fafb; }
        .badge-rupp { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-gold   { background: var(--rupp-gold-pale); color: #92680a; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-gray   { background: #f3f4f6; color: #6b7280; }
        .badge-orange { background: #ffedd5; color: #c2410c; }
        .btn-rupp-primary { background: var(--rupp-green); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-rupp-primary:hover { background: var(--rupp-green-light); color: #fff; }
        .btn-rupp-gold { background: var(--rupp-gold); color: var(--rupp-green-dark); border: none; padding: 8px 18px; border-radius: 8px; font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .alert-success-rupp { background: #f0fdf4; border: 1px solid #86efac; color: #166534; border-radius: 8px; padding: 10px 16px; font-size: 13.5px; display: flex; align-items: center; gap: 8px; }
        .alert-error-rupp { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 8px; padding: 10px 16px; font-size: 13.5px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .page-header-left h1 { font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 2px; }
        .page-header-left .breadcrumb-text { font-size: 12px; color: #9ca3af; }
        .stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; }
        .stat-card-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .stat-card-icon.green { background: #dcfce7; color: var(--rupp-green); }
        .stat-card-icon.gold  { background: var(--rupp-gold-pale); color: #92680a; }
        .stat-card-icon.blue  { background: #dbeafe; color: #1d4ed8; }
        .stat-card-info .label { font-size: 12px; color: #6b7280; margin-bottom: 2px; }
        .stat-card-info .value { font-size: 26px; font-weight: 600; color: #111827; line-height: 1; }

        /* ── Buttons ─────────────────────────────────────── */
        .btn-rupp-outline { background: transparent; color: var(--rupp-green); border: 1px solid var(--rupp-green); padding: 7px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all .15s; }
        .btn-rupp-outline:hover { background: var(--rupp-green); color: #fff; }
        .btn-icon { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; border: none; cursor: pointer; text-decoration: none; }
        .btn-icon.edit   { background: #dbeafe; color: #1d4ed8; }
        .btn-icon.delete { background: #fee2e2; color: #dc2626; }
        .btn-icon.view   { background: #dcfce7; color: #16a34a; }

        /* ── Forms ───────────────────────────────────────── */
        .form-label-rupp { font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; display: block; }
        .form-control-rupp, .form-select-rupp { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13.5px; color: #111827; background: #fff; transition: border-color .15s, box-shadow .15s; outline: none; font-family: 'Inter', sans-serif; }
        .form-control-rupp:focus, .form-select-rupp:focus { border-color: var(--rupp-green); box-shadow: 0 0 0 3px rgba(30,77,43,.12); }
        .form-error { font-size: 12px; color: #dc2626; margin-top: 4px; }

        /* ── Green header strip ──────────────────────────── */
        .rupp-header-strip { background: var(--rupp-green); padding: 14px 20px; display: flex; align-items: center; gap: 10px; }
        .rupp-header-strip h5 { color: #fff; font-size: 14px; font-weight: 600; margin: 0; }
        .rupp-header-strip i { color: var(--rupp-gold); font-size: 16px; }

        /* ── Breadcrumb ──────────────────────────────────── */
        .page-header-left .breadcrumb-text { font-size: 12px; color: #9ca3af; }
        .page-header-left .breadcrumb-text a { color: var(--rupp-green); text-decoration: none; }

        .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99; }
        .sidebar-overlay.active { display:block; }
        .mobile-menu-btn { display:none; background:none; border:none; cursor:pointer; padding:6px; color:var(--rupp-green); font-size:22px; margin-right:8px; }
        @media (max-width: 768px) {
            .sidebar { transform:translateX(-100%); transition:transform .25s ease; z-index:200; }
            .sidebar.open { transform:translateX(0); }
            .main-wrapper { margin-left:0 !important; }
            .mobile-menu-btn { display:flex; align-items:center; }
            .topbar { padding:0 16px; }
            .page-content { padding:16px; }
            .topbar-date { display:none; }
            .page-header { flex-direction:column; align-items:flex-start; gap:12px; }
            .stat-card-info .value { font-size:22px; }
        }
        @media (max-width: 480px) { .page-content { padding:12px; } }
    </style>
    @stack('styles')
</head>
<body>

<aside class="sidebar">
    <a href="{{ route('student.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('images/rupp-logo.svg') }}" alt="RUPP">
        <div class="sidebar-brand-text">
            <span class="title">RUPP — SMS</span>
            <span class="subtitle">Student Management System</span>
        </div>
    </a>

    <div class="sidebar-role-badge">
        <i class="bi bi-mortarboard-fill"></i> Student Portal
    </div>

    <div class="sidebar-section-label">My Studies</div>
    <ul class="sidebar-nav">
        <li>
            <a href="{{ route('student.dashboard') }}"
               class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('student.courses.index') }}"
               class="{{ request()->routeIs('student.courses.*') ? 'active' : '' }}">
                <i class="bi bi-book"></i> My Courses
            </a>
        </li>
        <li>
            <a href="{{ route('student.timetable') }}"
               class="{{ request()->routeIs('student.timetable') ? 'active' : '' }}">
                <i class="bi bi-calendar3-week"></i> Timetable
            </a>
        </li>
    </ul>

    <div class="sidebar-section-label">Attendance</div>
    <ul class="sidebar-nav">
        <li>
            <a href="{{ route('student.attendance.records') }}"
               class="{{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
                <i class="bi bi-check2-square"></i> My Attendance
            </a>
        </li>
    </ul>

    <div class="sidebar-section-label">Academic Record</div>
    <ul class="sidebar-nav">
        <li>
            <a href="{{ route('student.transcript.index') }}"
               class="{{ request()->routeIs('student.transcript.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Transcript
            </a>
        </li>
        <li>
            <a href="{{ route('student.announcements.index') }}"
               class="{{ request()->routeIs('student.announcements.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone-fill"></i> Announcements
            </a>
        </li>
    </ul>

    <div class="sidebar-section-label">Account</div>
    <ul class="sidebar-nav">
        <li>
            <a href="{{ route('profile.edit') }}"
               class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                @if(Auth::user()->photo)
                    <img src="{{ Auth::user()->photo_url }}" alt="">
                @else
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="sidebar-user-info">
                <div class="name">{{ Auth::user()->name }}</div>
                <div class="role">
                    {{ Auth::user()->student?->student_id ?? 'Student' }}
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-logout" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="main-wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
    <header class="topbar">
        <div style="display:flex; align-items:center; gap:4px;">
            <button class="mobile-menu-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <span class="topbar-date" style="font-size: 12px; color: #6b7280;">
                <i class="bi bi-calendar3"></i> {{ now()->format('l, d F Y') }}
            </span>
            @php
                $student = Auth::user()?->student;
                $topbarSemester = $student ? \App\Models\Semester::forYearLevel($student->year_level) : null;
            @endphp
            @if($topbarSemester)
            <span class="badge-rupp {{ $topbarSemester->isRunning() ? 'badge-green' : 'badge-blue' }}" style="font-size:11px;">
                <i class="bi bi-circle-fill" style="font-size:6px;"></i>
                {{ $topbarSemester->name }}
                @if($topbarSemester->year_level) Yr{{ $topbarSemester->year_level }} @endif
                {{ $topbarSemester->academic_year }}
            </span>
            @endif
        </div>
    </header>

    <div style="padding: 0 28px; padding-top: 16px;">
        @if(session('success'))
            <div class="alert-success-rupp">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-error-rupp">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following:</strong>
                <ul style="margin: 6px 0 0; padding-left: 18px; font-size: 13px;">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
    </div>

    <main class="page-content">@yield('content')</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}
function closeSidebar() {
    document.querySelector('.sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
}
document.querySelectorAll('.sidebar-nav a').forEach(l => l.addEventListener('click', () => { if(window.innerWidth<=768) closeSidebar(); }));
</script>
@stack('scripts')
</body>
</html>