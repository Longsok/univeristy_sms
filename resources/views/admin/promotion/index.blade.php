@extends('layouts.admin')
@section('title', 'Year Promotion')
@section('page-title', 'Year Promotion')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Student Year Promotion</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Year Promotion
        </div>
    </div>
    <a href="{{ route('admin.promotion.history') }}" class="btn-rupp-outline">
        <i class="bi bi-clock-history"></i> Promotion History
    </a>
</div>

{{-- Info banner --}}
<div style="background:#fff7ed; border:1px solid #fdba74; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; gap:12px; align-items:flex-start;">
    <i class="bi bi-info-circle-fill" style="color:#f59e0b; font-size:18px; flex-shrink:0;"></i>
    <div style="font-size:13px; color:#6b7280; line-height:1.7;">
        <strong style="color:#c2410c;">How promotion works:</strong><br>
        1. Select a year level and program to view class groups.<br>
        2. Expand a class group → review each student's eligibility.<br>
        3. Check students to promote → click Promote.<br>
        4. If <strong>all students</strong> in a group are promoted, the group's year level updates automatically (e.g. M1 Year 1 → M1 Year 2).<br>
        5. A promotion record is saved for audit.
        <br><strong style="color:#166534;">Eligibility:</strong> GPA ≥ 1.0 · No Fail grades · No pending Re-exam.
    </div>
</div>

{{-- Filter --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <div>
                <label class="form-label-rupp" style="margin-bottom:4px;">Current Year Level</label>
                <select name="year_level" class="form-select-rupp" style="min-width:180px;" onchange="this.form.submit()">
                    @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" {{ $selectedYear == $i ? 'selected' : '' }}>
                        Year {{ $i }} → Year {{ $i + 1 }}
                    </option>
                    @endfor
                    <option value="6" {{ $selectedYear == 6 ? 'selected' : '' }}>
                        Year 6 → Graduate
                    </option>
                </select>
            </div>
            <div>
                <label class="form-label-rupp" style="margin-bottom:4px;">Program</label>
                <select name="program_id" class="form-select-rupp" style="min-width:260px;" onchange="this.form.submit()">
                    <option value="">All Programs</option>
                    @foreach($programs as $prog)
                    <option value="{{ $prog->id }}" {{ $selectedProg == $prog->id ? 'selected' : '' }}>
                        {{ $prog->name }} ({{ $prog->code }})
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

{{-- Summary --}}
@php
    $totalStudents = $groupStats->sum('total') + $ungrouped->count();
    $totalEligible = $groupStats->sum('eligible') + $ungrouped->where('eligible', true)->count();
    $totalNot      = $groupStats->sum('notEligible') + $ungrouped->where('eligible', false)->count();
@endphp
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="bi bi-people-fill"></i></div>
        <div class="stat-card-info"><div class="label">Total Students</div><div class="value">{{ $totalStudents }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="bi bi-check-circle-fill"></i></div>
        <div class="stat-card-info"><div class="label">Eligible</div><div class="value" style="color:#166534;">{{ $totalEligible }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon" style="background:#fee2e2; color:#dc2626;"><i class="bi bi-x-circle-fill"></i></div>
        <div class="stat-card-info"><div class="label">Not Eligible</div><div class="value" style="color:#dc2626;">{{ $totalNot }}</div></div>
    </div>
</div>

{{-- Class Group Cards --}}
@forelse($groupStats as $stat)
@php $group = $stat['group']; @endphp
<div class="card-rupp" style="margin-bottom:14px;">

    {{-- Header — clickable --}}
    <div onclick="toggleGroup('group-{{ $group->id }}', this)" style="cursor:pointer; user-select:none;">
        <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:16px;">

                {{-- Group badge --}}
                <div style="width:56px; height:56px; background:var(--rupp-green); border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0;">
                    <span style="font-size:16px; font-weight:800; color:var(--rupp-gold); line-height:1;">{{ $group->name }}</span>
                    <span style="font-size:9px; color:rgba(255,255,255,0.6); margin-top:2px;">YR {{ $group->year_level }}</span>
                </div>

                <div>
                    <div style="font-size:15px; font-weight:600; color:#111827;">
                        Class {{ $group->name }}
                        <span style="font-size:12px; color:#9ca3af; font-weight:400; margin-left:8px;">
                            Year {{ $group->year_level }} → Year {{ $group->year_level + 1 }}
                        </span>
                    </div>
                    <div style="font-size:12.5px; color:#6b7280; margin-top:2px;">
                        {{ $group->program->code }} · {{ $group->program->name }}
                    </div>
                    @if($group->description)
                    <div style="font-size:11.5px; color:#9ca3af; margin-top:1px;">{{ $group->description }}</div>
                    @endif
                </div>
            </div>

            {{-- Stats --}}
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="text-align:center; background:#f9fafb; border-radius:8px; padding:8px 16px;">
                    <div style="font-size:20px; font-weight:700; color:#374151;">{{ $stat['total'] }}</div>
                    <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em;">Students</div>
                </div>
                <div style="text-align:center; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:8px 16px;">
                    <div style="font-size:20px; font-weight:700; color:#166534;">{{ $stat['eligible'] }}</div>
                    <div style="font-size:10px; color:#16a34a; text-transform:uppercase; letter-spacing:.04em;">Eligible</div>
                </div>
                @if($stat['notEligible'] > 0)
                <div style="text-align:center; background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:8px 16px;">
                    <div style="font-size:20px; font-weight:700; color:#dc2626;">{{ $stat['notEligible'] }}</div>
                    <div style="font-size:10px; color:#dc2626; text-transform:uppercase; letter-spacing:.04em;">Issues</div>
                </div>
                @endif
                <div style="text-align:center; background:#f0fdf4; border-radius:8px; padding:8px 16px;">
                    <div style="font-size:20px; font-weight:700; color:var(--rupp-green);">{{ $stat['avgGpa'] }}</div>
                    <div style="font-size:10px; color:#9ca3af; text-transform:uppercase; letter-spacing:.04em;">Avg GPA</div>
                </div>
                <i class="bi bi-chevron-down expand-icon" style="font-size:18px; color:#9ca3af; transition:transform .2s;"></i>
            </div>
        </div>
    </div>

    {{-- Expandable --}}
    <div id="group-{{ $group->id }}" style="display:none; border-top:1px solid #f3f4f6;">
        <form method="POST" action="{{ $selectedYear < 6 ? route('admin.promotion.promote') : route('admin.promotion.graduate') }}">
            @csrf
            <input type="hidden" name="class_group_id" value="{{ $group->id }}">

            {{-- Academic year + notes --}}
            <div style="padding:14px 20px; background:#f9fafb; border-bottom:1px solid #f3f4f6; display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                <div>
                    <label class="form-label-rupp" style="margin-bottom:4px; font-size:11px;">Academic Year</label>
                    <input type="text" name="academic_year"
                        value="{{ date('Y').'-'.(date('Y')+1) }}"
                        class="form-control-rupp" style="width:130px; padding:6px 10px; font-size:13px;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label class="form-label-rupp" style="margin-bottom:4px; font-size:11px;">Notes (optional)</label>
                    <input type="text" name="notes"
                        placeholder="e.g. End of Semester 2 promotion"
                        class="form-control-rupp" style="padding:6px 10px; font-size:13px;">
                </div>
            </div>

            {{-- Student table --}}
            <div style="overflow-x:auto;">
                <table class="table-rupp">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox"
                                    onchange="toggleGroupCheck('chk-{{ $group->id }}', this.checked)"
                                    style="accent-color:var(--rupp-green);">
                            </th>
                            <th>Student</th>
                            <th style="text-align:center;">GPA</th>
                            <th style="text-align:center;">Semesters</th>
                            <th style="text-align:center;">Credits</th>
                            <th style="text-align:center;">Fails</th>
                            <th style="text-align:center;">Re-exam</th>
                            <th style="text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stat['students'] as $row)
                        <tr style="{{ !$row['eligible'] ? 'background:#fef2f2;' : '' }}">
                            <td>
                                <input type="checkbox" name="student_ids[]"
                                    value="{{ $row['student']->id }}"
                                    class="chk-{{ $group->id }}"
                                    {{ $row['eligible'] ? 'checked' : '' }}
                                    onchange="updateCount('{{ $group->id }}')"
                                    style="accent-color:var(--rupp-green);">
                            </td>
                            <td>
                                <div style="font-weight:500; font-size:13px;">{{ $row['student']->user->name }}</div>
                                @if($row['student']->user->name_kh)
                                <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $row['student']->user->name_kh }}</div>
                                @endif
                                <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $row['student']->student_id }}</div>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-weight:600; font-size:14px; color:{{ $row['gpa'] >= 2.0 ? 'var(--rupp-green)' : ($row['gpa'] >= 1.0 ? '#c2410c' : '#dc2626') }};">
                                    {{ number_format($row['gpa'], 2) }}
                                </span>
                            </td>
                            <td style="text-align:center; color:#6b7280;">{{ $row['credits'] }}</td>
                            <td style="text-align:center;">
                                @if($row['fails'] > 0)
                                    <span class="badge-rupp badge-red">{{ $row['fails'] }} Fail</span>
                                @else <span style="color:#9ca3af; font-size:12px;">—</span> @endif
                            </td>
                            <td style="text-align:center;">
                                @if($row['reexam'] > 0)
                                    <span class="badge-rupp badge-orange">{{ $row['reexam'] }} Pending</span>
                                @else <span style="color:#9ca3af; font-size:12px;">—</span> @endif
                            </td>
                            <td style="text-align:center;">
                                @if($row['eligible'])
                                    <span class="badge-rupp badge-green"><i class="bi bi-check-lg"></i> Eligible</span>
                                @else
                                    <span class="badge-rupp badge-red"><i class="bi bi-x-lg"></i> Hold</span>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                @if($row['semesters_complete'])
                                    <span class="badge-rupp badge-green">
                                        <i class="bi bi-check-lg"></i> {{ $row['finalised_semesters'] }}/2
                                    </span>
                                @else
                                    <span class="badge-rupp badge-red">
                                        {{ $row['finalised_semesters'] }}/2
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div style="padding:14px 20px; background:#f9fafb; border-top:1px solid #f3f4f6; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                @if($selectedYear < 6)
                <button type="submit" class="btn-rupp-primary"
                    onclick="return confirmPromote('{{ $group->id }}', '{{ $group->name }}', {{ $selectedYear }})">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                    Promote → Year {{ $selectedYear + 1 }}
                </button>
                @else
                <button type="submit" class="btn-rupp-gold"
                    onclick="return confirm('Graduate selected students from {{ $group->name }}?')">
                    <i class="bi bi-mortarboard-fill"></i> Graduate
                </button>
                @endif

                <button type="button" class="btn-rupp-outline" style="font-size:12px; padding:7px 12px;"
                    onclick="selectEligible('chk-{{ $group->id }}', [{{ $stat['students']->where('eligible',true)->map(fn($r)=>$r['student']->id)->join(',') }}])">
                    <i class="bi bi-check-all"></i> Select Eligible Only
                </button>

                <button type="button" class="btn-rupp-outline" style="font-size:12px; padding:7px 12px;"
                    onclick="toggleGroupCheck('chk-{{ $group->id }}', false); updateCount('{{ $group->id }}')">
                    <i class="bi bi-x"></i> Clear
                </button>

                <span style="font-size:12px; color:#9ca3af; margin-left:6px;">
                    <span id="count-{{ $group->id }}">{{ $stat['eligible'] }}</span>
                    / {{ $stat['total'] }} selected
                </span>

                @if($stat['eligible'] === $stat['total'])
                <span style="font-size:11.5px; color:#166534; background:#dcfce7; border:1px solid #86efac; border-radius:6px; padding:3px 10px;">
                    <i class="bi bi-check-circle-fill"></i> All eligible — group year will update to Year {{ $selectedYear + 1 }}
                </span>
                @else
                <span style="font-size:11.5px; color:#c2410c; background:#fff7ed; border:1px solid #fdba74; border-radius:6px; padding:3px 10px;">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ $stat['notEligible'] }} student(s) have issues — group year won't auto-update
                </span>
                @endif
            </div>
        </form>
    </div>
</div>
@empty
<div class="card-rupp" style="margin-bottom:14px;">
    <div class="card-rupp-body" style="text-align:center; padding:40px; color:#9ca3af;">
        <i class="bi bi-people" style="font-size:36px; display:block; margin-bottom:10px;"></i>
        <div style="font-size:14px; font-weight:500;">No class groups found for Year {{ $selectedYear }}.</div>
        <div style="font-size:12.5px; margin-top:4px;">
            Create class groups from <a href="{{ route('admin.class-groups.index') }}" style="color:var(--rupp-green);">Class Groups</a>.
        </div>
    </div>
</div>
@endforelse

{{-- Ungrouped students --}}
@if($ungrouped->count() > 0)
<div class="card-rupp" style="margin-bottom:14px; border:1px solid #fdba74;">
    <div onclick="toggleGroup('ungrouped', this)" style="cursor:pointer;">
        <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; background:#fffbeb; flex-wrap:wrap; gap:12px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:56px; height:56px; background:#f59e0b; border-radius:12px; display:flex; align-items:center; justify-content:center;">
                    <i class="bi bi-person-fill" style="font-size:22px; color:#fff;"></i>
                </div>
                <div>
                    <div style="font-size:15px; font-weight:600; color:#92400e;">Ungrouped Students</div>
                    <div style="font-size:12.5px; color:#a16207;">Not assigned to any class group</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="text-align:center; background:#fef3c7; border-radius:8px; padding:8px 14px;">
                    <div style="font-size:20px; font-weight:700; color:#92400e;">{{ $ungrouped->count() }}</div>
                    <div style="font-size:10px; color:#a16207; text-transform:uppercase;">Students</div>
                </div>
                <div style="text-align:center; background:#f0fdf4; border-radius:8px; padding:8px 14px;">
                    <div style="font-size:20px; font-weight:700; color:#166534;">{{ $ungrouped->where('eligible',true)->count() }}</div>
                    <div style="font-size:10px; color:#16a34a; text-transform:uppercase;">Eligible</div>
                </div>
                <i class="bi bi-chevron-down expand-icon" style="font-size:18px; color:#9ca3af; transition:transform .2s;"></i>
            </div>
        </div>
    </div>

    <div id="ungrouped" style="display:none; border-top:1px solid #fde68a;">
        <form method="POST" action="{{ $selectedYear < 6 ? route('admin.promotion.promote') : route('admin.promotion.graduate') }}">
            @csrf
            <div style="padding:12px 20px; background:#fffbeb; border-bottom:1px solid #fde68a; display:flex; gap:12px; flex-wrap:wrap;">
                <div>
                    <label class="form-label-rupp" style="margin-bottom:4px; font-size:11px;">Academic Year</label>
                    <input type="text" name="academic_year" value="{{ date('Y').'-'.(date('Y')+1) }}"
                        class="form-control-rupp" style="width:130px; padding:6px 10px; font-size:13px;">
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="table-rupp">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" onchange="toggleGroupCheck('chk-ungrouped', this.checked)" style="accent-color:var(--rupp-green);">
                            </th>
                            <th>Student</th>
                            <th>Program</th>
                            <th style="text-align:center;">GPA</th>
                            <th style="text-align:center;">Fails</th>
                            <th style="text-align:center;">Re-exam</th>
                            <th style="text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ungrouped as $row)
                        <tr style="{{ !$row['eligible'] ? 'background:#fef2f2;' : '' }}">
                            <td>
                                <input type="checkbox" name="student_ids[]" value="{{ $row['student']->id }}"
                                    class="chk-ungrouped" {{ $row['eligible'] ? 'checked' : '' }}
                                    style="accent-color:var(--rupp-green);">
                            </td>
                            <td>
                                <div style="font-weight:500;">{{ $row['student']->user->name }}</div>
                                <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $row['student']->student_id }}</div>
                            </td>
                            <td style="font-size:12.5px; color:#6b7280;">{{ $row['student']->program->code }}</td>
                            <td style="text-align:center; font-weight:600; color:{{ $row['gpa'] >= 1.0 ? 'var(--rupp-green)' : '#dc2626' }};">
                                {{ number_format($row['gpa'], 2) }}
                            </td>
                            <td style="text-align:center;">
                                @if($row['fails'] > 0) <span class="badge-rupp badge-red">{{ $row['fails'] }}</span>
                                @else <span style="color:#9ca3af;">—</span> @endif
                            </td>
                            <td style="text-align:center;">
                                @if($row['reexam'] > 0) <span class="badge-rupp badge-orange">{{ $row['reexam'] }}</span>
                                @else <span style="color:#9ca3af;">—</span> @endif
                            </td>
                            <td style="text-align:center;">
                                @if($row['eligible']) <span class="badge-rupp badge-green"><i class="bi bi-check-lg"></i> Eligible</span>
                                @else <span class="badge-rupp badge-red"><i class="bi bi-x-lg"></i> Hold</span> @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:14px 20px; background:#fffbeb; border-top:1px solid #fde68a; display:flex; gap:10px;">
                @if($selectedYear < 6)
                <button type="submit" class="btn-rupp-primary"
                    onclick="return confirm('Promote selected ungrouped students to Year {{ $selectedYear + 1 }}?')">
                    <i class="bi bi-arrow-up-circle-fill"></i> Promote → Year {{ $selectedYear + 1 }}
                </button>
                @else
                <button type="submit" class="btn-rupp-gold"
                    onclick="return confirm('Graduate selected students?')">
                    <i class="bi bi-mortarboard-fill"></i> Graduate
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endif

{{-- Recent promotion history --}}
@if($recentHistory->count() > 0)
<div class="card-rupp" style="margin-top:24px;">
    <div class="card-rupp-header">
        <h5><i class="bi bi-clock-history" style="color:var(--rupp-gold)"></i> Recent Promotions</h5>
        <a href="{{ route('admin.promotion.history') }}" class="btn-rupp-outline" style="font-size:12px; padding:5px 12px;">
            View All
        </a>
    </div>
    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class Group</th>
                    <th style="text-align:center;">From → To</th>
                    <th style="text-align:center;">GPA</th>
                    <th>Academic Year</th>
                    <th>Type</th>
                    <th>Promoted By</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentHistory as $h)
                <tr>
                    <td>
                        <div style="font-weight:500; font-size:13px;">{{ $h->student->user->name }}</div>
                        <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $h->student->student_id }}</div>
                    </td>
                    <td>
                        @if($h->classGroup)
                        <span class="badge-rupp badge-green">{{ $h->classGroup->name }}</span>
                        @else
                        <span style="color:#9ca3af; font-size:12px;">Ungrouped</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <span style="font-weight:600; color:#6b7280;">Year {{ $h->from_year }}</span>
                        <i class="bi bi-arrow-right" style="color:#9ca3af; margin:0 4px;"></i>
                        <span style="font-weight:600; color:{{ $h->type === 'graduation' ? 'var(--rupp-gold)' : 'var(--rupp-green)' }};">
                            {{ $h->type === 'graduation' ? 'Graduated' : 'Year '.$h->to_year }}
                        </span>
                    </td>
                    <td style="text-align:center; font-weight:600; color:var(--rupp-green);">
                        {{ number_format($h->gpa_at_promotion, 2) }}
                    </td>
                    <td style="font-size:12.5px; color:#6b7280;">{{ $h->academic_year }}</td>
                    <td>
                        <span class="badge-rupp {{ $h->type === 'graduation' ? 'badge-gold' : 'badge-green' }}" style="font-size:10px;">
                            {{ ucfirst($h->type) }}
                        </span>
                    </td>
                    <td style="font-size:12.5px; color:#6b7280;">{{ $h->promotedBy->name }}</td>
                    <td style="font-size:11.5px; color:#9ca3af;">{{ $h->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function toggleGroup(id, header) {
    const panel = document.getElementById(id);
    const icon  = header.querySelector('.expand-icon');
    if (!panel) return;
    const open = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : 'block';
    if (icon) icon.style.transform = open ? '' : 'rotate(180deg)';
}

function toggleGroupCheck(cls, checked) {
    document.querySelectorAll('.' + cls).forEach(cb => cb.checked = checked);
    const groupId = cls.replace('chk-', '');
    updateCount(groupId);
}

function selectEligible(cls, eligibleIds) {
    document.querySelectorAll('.' + cls).forEach(cb => {
        cb.checked = eligibleIds.includes(parseInt(cb.value));
    });
    const groupId = cls.replace('chk-', '');
    updateCount(groupId);
}

function updateCount(groupId) {
    const checked = document.querySelectorAll('.chk-' + groupId + ':checked').length;
    const el = document.getElementById('count-' + groupId);
    if (el) el.textContent = checked;
}

function confirmPromote(groupId, groupName, year) {
    const count = document.querySelectorAll('.chk-' + groupId + ':checked').length;
    if (count === 0) { alert('Please select at least one student.'); return false; }
    return confirm(`Promote ${count} student(s) from Class ${groupName} to Year ${year + 1}?`);
}
</script>
@endpush