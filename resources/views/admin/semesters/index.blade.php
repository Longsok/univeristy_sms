@extends('layouts.admin')
@section('title', 'Semesters')
@section('page-title', 'Semesters')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Academic Years & Semesters</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Semesters
        </div>
    </div>
    <button class="btn-rupp-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg"></i> Add Semester
    </button>
</div>

{{-- Info banner --}}
<div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; gap:12px; align-items:flex-start;">
    <i class="bi bi-lightbulb-fill" style="color:#2563eb; font-size:18px; flex-shrink:0; margin-top:1px;"></i>
    <div style="font-size:13px; color:#1e40af; line-height:1.7;">
        <strong>How to set up:</strong>
        For each academic year, create <strong>Semester 1 and Semester 2 per year level</strong>
        (since each year level has different dates).
        Example: Year 1 Sem 1 (Oct–Feb), Year 2 Sem 1 (Aug–Nov), Year 3 Sem 1 (Nov–Mar).
        Then set the academic year as <strong>Current</strong> — status updates automatically from dates.
    </div>
</div>

@foreach($byYear as $yearLabel => $yearSemesters)
@php
    $isCurrentYear = $yearLabel === $activeYear;
    $totalCourses  = $yearSemesters->sum('courses_count');

    // Group by year_level for display (null = All Years)
    $byYearLevel = $yearSemesters
        ->sortBy('year_level')
        ->groupBy(fn($s) => $s->year_level ?? 'all');

    // Count running (only relevant for current year)
    $runningCount = $isCurrentYear
        ? $yearSemesters->filter(fn($s) => $s->isRunning())->count()
        : 0;
@endphp

<div class="card-rupp" style="margin-bottom:24px; border:{{ $isCurrentYear ? '2px solid var(--rupp-green)' : '1px solid #e5e7eb' }};">

    {{-- Academic Year Header --}}
    <div style="background:{{ $isCurrentYear ? 'var(--rupp-green)' : '#f9fafb' }}; padding:18px 22px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:14px;">
            <div style="width:48px; height:48px; background:{{ $isCurrentYear ? 'rgba(201,162,39,.25)' : 'var(--rupp-green)' }}; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="bi bi-calendar2-fill" style="font-size:20px; color:{{ $isCurrentYear ? 'var(--rupp-gold)' : '#fff' }};"></i>
            </div>
            <div>
                <div style="font-size:20px; font-weight:700; color:{{ $isCurrentYear ? '#fff' : '#111827' }}; letter-spacing:-.02em;">
                    Academic Year {{ $yearLabel }}
                </div>
                <div style="font-size:12px; color:{{ $isCurrentYear ? 'rgba(255,255,255,.55)' : '#9ca3af' }}; margin-top:3px; display:flex; gap:14px; align-items:center;">
                    <span><i class="bi bi-collection"></i> {{ $yearSemesters->count() }} semesters</span>
                    <span><i class="bi bi-book"></i> {{ $totalCourses }} courses</span>
                    @if($isCurrentYear && $runningCount > 0)
                    <span style="color:{{ $isCurrentYear ? '#86efac' : '#16a34a' }}; font-weight:600;">
                        <i class="bi bi-circle-fill" style="font-size:7px;"></i>
                        {{ $runningCount }} running now
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            @if($isCurrentYear)
                <span style="background:var(--rupp-gold); color:#1a3a1f; border-radius:20px; padding:5px 16px; font-size:12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                    <i class="bi bi-circle-fill" style="font-size:7px;"></i> Current Year
                </span>
                <button onclick="openAddForYear('{{ $yearLabel }}')"
                    style="background:rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.3); border-radius:8px; padding:6px 14px; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                    <i class="bi bi-plus-lg"></i> Add Semester
                </button>
            @else
                <button onclick="openAddForYear('{{ $yearLabel }}')"
                    class="btn-rupp-outline" style="padding:5px 12px; font-size:12px;">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
                <form action="{{ route('admin.semesters.set-active-year') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="academic_year" value="{{ $yearLabel }}">
                    <button type="submit" class="btn-rupp-primary" style="padding:6px 14px; font-size:12px;"
                        onclick="return confirm('Set {{ $yearLabel }} as current academic year?\n\nAll semesters in this year will activate. Status (Running/Upcoming) will be based on their dates automatically.')">
                        <i class="bi bi-play-circle-fill"></i> Set as Current
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Semesters grid — grouped by year level --}}
    <div style="padding:20px 22px;">

        @if($yearSemesters->isEmpty())
        <div style="text-align:center; padding:24px; color:#9ca3af; border:2px dashed #e5e7eb; border-radius:10px;">
            <i class="bi bi-calendar-plus" style="font-size:28px; display:block; margin-bottom:8px;"></i>
            <div style="font-size:13px;">No semesters yet for {{ $yearLabel }}.</div>
            <button onclick="openAddForYear('{{ $yearLabel }}')" class="btn-rupp-outline" style="margin-top:10px; font-size:12px; padding:5px 14px;">
                <i class="bi bi-plus-lg"></i> Add First Semester
            </button>
        </div>
        @else

        {{-- Display: each row = one year level, columns = Sem 1 | Sem 2 --}}
        <div style="display:flex; flex-direction:column; gap:16px;">
            @foreach($byYearLevel as $yrKey => $yrSemesters)
            @php
                $yrLabel = $yrKey === 'all' ? 'All Year Levels' : "Year {$yrKey}";
                $sem1 = $yrSemesters->firstWhere('semester_number', 1);
                $sem2 = $yrSemesters->firstWhere('semester_number', 2);
            @endphp

            <div>
                {{-- Year level row label --}}
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                    <span style="background:{{ $yrKey === 'all' ? '#f3e8ff' : '#dbeafe' }}; color:{{ $yrKey === 'all' ? '#7c3aed' : '#1e40af' }}; border-radius:8px; padding:3px 12px; font-size:12px; font-weight:600; white-space:nowrap;">
                        {{ $yrLabel }}
                    </span>
                    <div style="height:1px; flex:1; background:#f3f4f6;"></div>
                    <button onclick="openAddModal('{{ $yearLabel }}', '{{ $yrKey === 'all' ? '' : $yrKey }}')"
                        style="background:none; border:1px dashed #d1d5db; border-radius:6px; padding:2px 10px; font-size:11px; color:#9ca3af; cursor:pointer; white-space:nowrap; transition:all .15s;"
                        onmouseover="this.style.borderColor='var(--rupp-green)'; this.style.color='var(--rupp-green)'"
                        onmouseout="this.style.borderColor='#d1d5db'; this.style.color='#9ca3af'">
                        <i class="bi bi-plus-lg"></i> Add semester
                    </button>
                </div>

                {{-- Sem 1 + Sem 2 side by side --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    @foreach([1 => $sem1, 2 => $sem2] as $semNum => $sem)
                    @if($sem)
                    @php
                        $running   = $sem->isRunning();
                        $upcoming  = $sem->isUpcoming();
                        $completed = $sem->isCompleted();
                        $progress  = $sem->progress;

                        $sc = match(true) {
                            $running   => ['bg'=>'#f0fdf4','border'=>'#86efac','tbg'=>'#dcfce7','tc'=>'#166534','label'=>'Running'],
                            $upcoming  => ['bg'=>'#eff6ff','border'=>'#bfdbfe','tbg'=>'#dbeafe','tc'=>'#1e40af','label'=>'Upcoming'],
                            default    => ['bg'=>'#f9fafb','border'=>'#e5e7eb','tbg'=>'#f3f4f6','tc'=>'#6b7280','label'=>'Completed'],
                        };
                    @endphp
                    <div style="background:{{ $sc['bg'] }}; border:1.5px solid {{ $sc['border'] }}; border-radius:10px; padding:14px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                            <div style="font-size:13.5px; font-weight:700; color:#111827;">
                                Semester {{ $semNum }}
                                <span style="font-size:11px; color:#9ca3af; font-weight:400; margin-left:4px;">{{ $sem->name }}</span>
                            </div>
                            <span style="background:{{ $sc['tbg'] }}; color:{{ $sc['tc'] }}; border-radius:20px; padding:2px 10px; font-size:10.5px; font-weight:600; white-space:nowrap; display:inline-flex; align-items:center; gap:3px;">
                                @if($running)<i class="bi bi-circle-fill" style="font-size:5px;"></i>@endif
                                {{ $sc['label'] }}
                            </span>
                        </div>

                        {{-- Dates --}}
                        <div style="font-size:12px; color:#374151; margin-bottom:6px; display:flex; align-items:center; gap:5px;">
                            <i class="bi bi-calendar3" style="color:var(--rupp-green); font-size:11px;"></i>
                            {{ $sem->start_date->format('d M Y') }}
                            <i class="bi bi-arrow-right" style="color:#d1d5db; font-size:9px;"></i>
                            {{ $sem->end_date->format('d M Y') }}
                        </div>

                        {{-- Meta --}}
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:8px; display:flex; gap:10px;">
                            <span><i class="bi bi-clock"></i> {{ round($sem->start_date->diffInWeeks($sem->end_date),1) }} wks</span>
                            <span><i class="bi bi-book"></i> {{ $sem->courses_count }}</span>
                        </div>

                        {{-- Progress --}}
                        @if($running && $progress > 0)
                        <div style="margin-bottom:8px;">
                            <div style="display:flex; justify-content:space-between; font-size:10px; color:#9ca3af; margin-bottom:3px;">
                                <span>Progress</span><span style="color:#166534; font-weight:600;">{{ $progress }}%</span>
                            </div>
                            <div style="height:4px; background:#dcfce7; border-radius:2px; overflow:hidden;">
                                <div style="height:100%; width:{{ $progress }}%; background:var(--rupp-green); border-radius:2px;"></div>
                            </div>
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div style="display:flex; gap:6px;">
                            <button onclick="openEdit({{ $sem->id }}, '{{ addslashes($sem->name) }}', '{{ $sem->start_date->format('Y-m-d') }}', '{{ $sem->end_date->format('Y-m-d') }}', '{{ $sem->year_level ?? '' }}')"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px; flex:1; justify-content:center;">
                                <i class="bi bi-pencil-fill"></i> Edit
                            </button>
                            @if($sem->courses_count == 0)
                            <form action="{{ route('admin.semesters.destroy', $sem) }}" method="POST"
                                onsubmit="return confirm('Delete Semester {{ $semNum }} for {{ $yrLabel }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-icon delete" style="width:28px; height:28px;">
                                    <i class="bi bi-trash-fill" style="font-size:11px;"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    @else
                    {{-- Empty slot — add this semester --}}
                    <div onclick="openAddModal('{{ $yearLabel }}', '{{ $yrKey === 'all' ? '' : $yrKey }}', {{ $semNum }})"
                        style="border:2px dashed #e5e7eb; border-radius:10px; padding:14px; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:100px; cursor:pointer; transition:all .15s; color:#9ca3af;"
                        onmouseover="this.style.borderColor='var(--rupp-green)'; this.style.color='var(--rupp-green)'"
                        onmouseout="this.style.borderColor='#e5e7eb'; this.style.color='#9ca3af'">
                        <i class="bi bi-plus-circle" style="font-size:20px; margin-bottom:5px;"></i>
                        <div style="font-size:11.5px; font-weight:500;">Add Semester {{ $semNum }}</div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endforeach

@if($byYear->isEmpty())
<div class="card-rupp">
    <div style="text-align:center; padding:60px 20px; color:#9ca3af;">
        <i class="bi bi-calendar-x" style="font-size:48px; display:block; margin-bottom:16px; opacity:.4;"></i>
        <div style="font-size:16px; font-weight:600; color:#374151; margin-bottom:6px;">No semesters yet</div>
        <div style="font-size:13px; margin-bottom:20px;">Create your first academic year with year-level specific semesters.</div>
        <button class="btn-rupp-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Add First Semester
        </button>
    </div>
</div>
@endif

{{-- ── Add Modal ─────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:500px;">
        <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none;">
            <div class="rupp-header-strip"><i class="bi bi-calendar-plus-fill"></i><h5>Add Semester</h5></div>
            <div style="padding:24px;">
                <form method="POST" action="{{ route('admin.semesters.store') }}">
                    @csrf

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                        <div>
                            <label class="form-label-rupp">Academic Year <span style="color:#ef4444">*</span></label>
                            <input type="text" name="academic_year" id="addAcademicYear"
                                class="form-control-rupp" placeholder="2025-2026" required>
                            <div style="font-size:10.5px; color:#9ca3af; margin-top:3px;">Format: YYYY-YYYY</div>
                        </div>
                        <div>
                            <label class="form-label-rupp">Semester <span style="color:#ef4444">*</span></label>
                            <select name="semester_number" id="addSemNum" class="form-select-rupp" required>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>

                    {{-- Year Level --}}
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Year Level <span style="color:#ef4444">*</span></label>
                        <select name="year_level" id="addYearLevel" class="form-select-rupp">
                            <option value="">All Year Levels (same dates for everyone)</option>
                            @for($i=1;$i<=6;$i++)
                            <option value="{{ $i }}">Year {{ $i }}</option>
                            @endfor
                        </select>
                        <div style="font-size:11px; color:#6b7280; margin-top:6px; padding:8px 12px; background:#fffbeb; border:1px solid #fde68a; border-radius:6px; line-height:1.5;">
                            <strong>Your setup:</strong> Each year level has different dates →
                            select the specific year level. Create one entry per year level per semester.
                        </div>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Semester Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" id="addName" class="form-control-rupp" placeholder="e.g. Semester 1" required>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                        <div>
                            <label class="form-label-rupp">Start Date <span style="color:#ef4444">*</span></label>
                            <input type="date" name="start_date" id="addStart" class="form-control-rupp" required>
                        </div>
                        <div>
                            <label class="form-label-rupp">End Date <span style="color:#ef4444">*</span></label>
                            <input type="date" name="end_date" id="addEnd" class="form-control-rupp" required>
                        </div>
                    </div>

                    {{-- Quick setup guide --}}
                    <div style="margin-bottom:18px; padding:12px 14px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px;">
                        <div style="font-size:11px; font-weight:600; color:#166534; margin-bottom:6px;">
                            <i class="bi bi-lightbulb-fill"></i> Quick Setup Guide
                        </div>
                        <div style="font-size:11px; color:#166534; line-height:1.6;">
                            For academic year 2025-2026, create:<br>
                            Year 1 Sem 1 (Oct–Feb) + Year 1 Sem 2 (Mar–Jul)<br>
                            Year 2 Sem 1 (Aug–Nov) + Year 2 Sem 2 (Jan–May)<br>
                            Year 3 Sem 1 (Nov–Mar) + Year 3 Sem 2 (May–Aug)<br>
                            Year 4 Sem 1 (Nov–Mar) + Year 4 Sem 2 (May–Aug)
                        </div>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-rupp-primary">
                            <i class="bi bi-check-lg"></i> Save Semester
                        </button>
                        <button type="button" class="btn-rupp-outline" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Modal ────────────────────────────────────────────────────────────── --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:440px;">
        <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none;">
            <div class="rupp-header-strip"><i class="bi bi-pencil-fill"></i><h5>Edit Semester</h5></div>
            <div style="padding:24px;">
                <form id="editForm" method="POST" action="">
                    @csrf @method('PUT')
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Name</label>
                        <input type="text" name="name" id="editName" class="form-control-rupp" required>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Year Level</label>
                        <select name="year_level" id="editYearLevel" class="form-select-rupp">
                            <option value="">All Year Levels</option>
                            @for($i=1;$i<=6;$i++)
                            <option value="{{ $i }}">Year {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
                        <div>
                            <label class="form-label-rupp">Start Date</label>
                            <input type="date" name="start_date" id="editStart" class="form-control-rupp" required>
                        </div>
                        <div>
                            <label class="form-label-rupp">End Date</label>
                            <input type="date" name="end_date" id="editEnd" class="form-control-rupp" required>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-rupp-primary">
                            <i class="bi bi-floppy-fill"></i> Update
                        </button>
                        <button type="button" class="btn-rupp-outline" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, name, start, end, yearLevel) {
    document.getElementById('editForm').action     = `/admin/semesters/${id}`;
    document.getElementById('editName').value      = name;
    document.getElementById('editStart').value     = start;
    document.getElementById('editEnd').value       = end;
    document.getElementById('editYearLevel').value = yearLevel || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function openAddForYear(year, semNum) {
    document.getElementById('addAcademicYear').value = year;
    if (semNum) document.getElementById('addSemNum').value = semNum;
    new bootstrap.Modal(document.getElementById('addModal')).show();
}

function openAddModal(year, yearLevel, semNum) {
    document.getElementById('addAcademicYear').value = year;
    document.getElementById('addYearLevel').value    = yearLevel || '';
    if (semNum) document.getElementById('addSemNum').value = semNum;
    // Auto-name
    const names = {1:'Semester 1', 2:'Semester 2'};
    if (semNum) document.getElementById('addName').value = names[semNum] || '';
    new bootstrap.Modal(document.getElementById('addModal')).show();
}
</script>
@endpush