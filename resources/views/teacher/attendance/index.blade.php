@extends('layouts.teacher')
@section('title', 'Attendance')
@section('page-title', 'Attendance')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $section->course->name }} — {{ $section->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('teacher.courses.index') }}">Courses</a> / Attendance
        </div>
    </div>
    <a href="{{ route('teacher.courses.index') }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

{{-- Legend --}}
<div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; align-items:center;">
    <span style="font-size:12px; font-weight:600; color:#6b7280;">Status:</span>
    <span style="background:#dcfce7; color:#166534; border:1px solid #86efac; border-radius:6px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;" onclick="setAll('present')">✓ Present</span>
    <span style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; border-radius:6px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;" onclick="setAll('absent')">✗ Absent</span>
    <span style="background:#ffedd5; color:#c2410c; border:1px solid #fdba74; border-radius:6px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;" onclick="setAll('late')">⏱ Late</span>
    <span style="background:#dbeafe; color:#1e40af; border:1px solid #93c5fd; border-radius:6px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;" onclick="setAll('permission')">📋 Permission</span>
    <span style="font-size:11px; color:#9ca3af; margin-left:8px;">Click any cell to cycle status. Click colored label to fill entire column.</span>
</div>

{{-- Score explanation --}}
<div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:10px 16px; margin-bottom:16px; font-size:12.5px; color:#166534; display:flex; align-items:center; gap:8px;">
    <i class="bi bi-info-circle-fill"></i>
    Score out of 10: Present = 1pt · Late = 0.5pt · Permission = 0.5pt · Absent = 0pt · Total 16 weeks = 10pts max
</div>

{{-- Weekly grid --}}
<div class="card-rupp" style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; min-width:1200px;">
        <thead>
            <tr>
                <th style="padding:10px 14px; background:#f9fafb; border-bottom:2px solid var(--rupp-green); text-align:left; font-size:12px; font-weight:600; color:#374151; position:sticky; left:0; z-index:5; min-width:180px; white-space:nowrap;">
                    Student
                </th>
                @foreach($weeks as $week)
                <th style="padding:8px 4px; background:#f9fafb; border-bottom:2px solid var(--rupp-green); text-align:center; font-size:11px; font-weight:600; color:#6b7280; min-width:52px; cursor:pointer;"
                    onclick="fillWeekColumn({{ $week }})"
                    title="Click to fill Week {{ $week }} for all students">
                    W{{ $week }}
                </th>
                @endforeach
                <th style="padding:10px 8px; background:#f9fafb; border-bottom:2px solid var(--rupp-green); text-align:center; font-size:11px; font-weight:600; color:#166534; min-width:48px;">P</th>
                <th style="padding:10px 8px; background:#f9fafb; border-bottom:2px solid var(--rupp-green); text-align:center; font-size:11px; font-weight:600; color:#991b1b; min-width:48px;">A</th>
                <th style="padding:10px 8px; background:#f9fafb; border-bottom:2px solid var(--rupp-green); text-align:center; font-size:11px; font-weight:600; color:#c2410c; min-width:48px;">L</th>
                <th style="padding:10px 8px; background:#f9fafb; border-bottom:2px solid var(--rupp-green); text-align:center; font-size:11px; font-weight:600; color:#1e40af; min-width:48px;">Pm</th>
                <th style="padding:10px 8px; background:var(--rupp-green); border-bottom:2px solid var(--rupp-green); text-align:center; font-size:11px; font-weight:700; color:var(--rupp-gold); min-width:60px;">/10</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $i => $row)
            @php $student = $row['student']; @endphp
            <tr data-student="{{ $student->id }}" style="{{ $i % 2 === 0 ? '' : 'background:#fafafa;' }}">
                {{-- Student name --}}
                <td style="padding:8px 14px; border-bottom:1px solid #f3f4f6; position:sticky; left:0; background:{{ $i % 2 === 0 ? '#fff' : '#fafafa' }}; z-index:4;">
                    <div style="font-size:13px; font-weight:500; color:#111827;">{{ $student->user->name }}</div>
                    @if($student->user->name_kh)
                    <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $student->user->name_kh }}</div>
                    @endif
                    <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $student->student_id }}</div>
                </td>

                {{-- Week cells --}}
                @foreach($weeks as $week)
                @php
                    $record = $row['weekRecords']->get($week);
                    $status = $record?->status ?? '';
                    $styles = match($status) {
                        'present'    => 'background:#dcfce7; color:#166534; border:1px solid #86efac;',
                        'absent'     => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
                        'late'       => 'background:#ffedd5; color:#c2410c; border:1px solid #fdba74;',
                        'permission' => 'background:#dbeafe; color:#1e40af; border:1px solid #93c5fd;',
                        default      => 'background:#f9fafb; color:#d1d5db; border:1px solid #e5e7eb;',
                    };
                    $label = match($status) {
                        'present'    => '✓',
                        'absent'     => '✗',
                        'late'       => '⏱',
                        'permission' => '📋',
                        default      => '—',
                    };
                @endphp
                <td style="padding:4px; border-bottom:1px solid #f3f4f6; text-align:center;">
                    <div class="att-cell"
                        data-student="{{ $student->id }}"
                        data-week="{{ $week }}"
                        data-status="{{ $status }}"
                        data-section="{{ $section->id }}"
                        style="{{ $styles }} border-radius:6px; width:40px; height:34px; display:inline-flex; align-items:center; justify-content:center; font-size:14px; cursor:pointer; margin:0 auto; user-select:none; transition:all .1s;"
                        title="Week {{ $week }} — click to change"
                        onclick="cycleStatus(this)">
                        {{ $label }}
                    </div>
                </td>
                @endforeach

                {{-- Summary counts --}}
                <td style="padding:8px; border-bottom:1px solid #f3f4f6; text-align:center;" class="count-present-{{ $student->id }}">
                    <span style="font-weight:600; color:#166534;">{{ $row['counts']['present'] }}</span>
                </td>
                <td style="padding:8px; border-bottom:1px solid #f3f4f6; text-align:center;" class="count-absent-{{ $student->id }}">
                    <span style="font-weight:600; color:#991b1b;">{{ $row['counts']['absent'] }}</span>
                </td>
                <td style="padding:8px; border-bottom:1px solid #f3f4f6; text-align:center;" class="count-late-{{ $student->id }}">
                    <span style="font-weight:600; color:#c2410c;">{{ $row['counts']['late'] }}</span>
                </td>
                <td style="padding:8px; border-bottom:1px solid #f3f4f6; text-align:center;" class="count-permission-{{ $student->id }}">
                    <span style="font-weight:600; color:#1e40af;">{{ $row['counts']['permission'] }}</span>
                </td>
                <td style="padding:8px; border-bottom:1px solid #f3f4f6; text-align:center;" class="score-{{ $student->id }}">
                    <span style="font-weight:700; font-size:13px; color:var(--rupp-green);">{{ $row['score'] }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($weeks) + 6 }}" style="text-align:center; padding:40px; color:#9ca3af;">
                    No students enrolled in this section.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Selected week bulk save --}}
<div class="card-rupp" style="margin-top:16px;">
    <div class="card-rupp-body" style="padding:14px 20px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <span style="font-size:13px; font-weight:500; color:#374151;">
            <i class="bi bi-info-circle" style="color:var(--rupp-green);"></i>
            Attendance saves automatically when you click each cell.
        </span>
        <span id="saveStatus" style="font-size:12px; color:#9ca3af;"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
const statuses = ['present', 'absent', 'late', 'permission', ''];
const styleMap = {
    'present':    { bg:'#dcfce7', color:'#166534', border:'#86efac', label:'✓' },
    'absent':     { bg:'#fee2e2', color:'#991b1b', border:'#fca5a5', label:'✗' },
    'late':       { bg:'#ffedd5', color:'#c2410c', border:'#fdba74', label:'⏱' },
    'permission': { bg:'#dbeafe', color:'#1e40af', border:'#93c5fd', label:'📋' },
    '':           { bg:'#f9fafb', color:'#d1d5db', border:'#e5e7eb', label:'—' },
};

let saveTimeout = null;

function applyStyle(cell, status) {
    const s = styleMap[status] || styleMap[''];
    cell.style.background = s.bg;
    cell.style.color      = s.color;
    cell.style.border     = `1px solid ${s.border}`;
    cell.textContent      = s.label;
    cell.dataset.status   = status;
}

function cycleStatus(cell) {
    const current = cell.dataset.status || '';
    const idx     = statuses.indexOf(current);
    const next    = statuses[(idx + 1) % statuses.length];

    applyStyle(cell, next);
    saveRecord(cell.dataset.section, cell.dataset.student, cell.dataset.week, next);
}

function saveRecord(sectionId, studentId, week, status) {
    // Show saving indicator
    document.getElementById('saveStatus').textContent = 'Saving...';

    fetch(`/teacher/attendance/${sectionId}/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ student_id: studentId, week: week, status: status }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update counts
            document.querySelector(`.count-present-${studentId} span`).textContent    = data.counts.present;
            document.querySelector(`.count-absent-${studentId} span`).textContent     = data.counts.absent;
            document.querySelector(`.count-late-${studentId} span`).textContent       = data.counts.late;
            document.querySelector(`.count-permission-${studentId} span`).textContent = data.counts.permission;
            document.querySelector(`.score-${studentId} span`).textContent            = data.score;

            document.getElementById('saveStatus').textContent = '✓ Saved';
            setTimeout(() => document.getElementById('saveStatus').textContent = '', 1500);
        }
    })
    .catch(() => {
        document.getElementById('saveStatus').textContent = '⚠ Save failed';
    });
}

// Fill entire week column with one status
function fillWeekColumn(week) {
    const status = prompt(`Set ALL students for Week ${week} to:\n(present / absent / late / permission)`);
    if (!['present','absent','late','permission'].includes(status)) return;

    const cells = document.querySelectorAll(`.att-cell[data-week="${week}"]`);
    cells.forEach(cell => {
        applyStyle(cell, status);
        saveRecord(cell.dataset.section, cell.dataset.student, week, status);
    });
}

// Fill all empty cells with one status
function setAll(status) {
    const cells = document.querySelectorAll('.att-cell[data-status=""]');
    if (cells.length === 0) {
        alert('No empty cells to fill.');
        return;
    }
    if (!confirm(`Fill ${cells.length} empty cells with "${status}"?`)) return;
    cells.forEach(cell => {
        applyStyle(cell, status);
        saveRecord(cell.dataset.section, cell.dataset.student, cell.dataset.week, status);
    });
}
</script>
@endpush