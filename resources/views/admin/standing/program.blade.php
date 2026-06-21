@extends('layouts.admin')
@section('title', '{{ $program->name }} — Academic Standing')
@section('page-title', 'Academic Standing')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $program->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.standing.index') }}">Academic Standing</a> /
            {{ $program->code }}
        </div>
    </div>
    <a href="{{ route('admin.standing.index') }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> All Programs
    </a>
</div>

{{-- Summary cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:20px;">
    @foreach([
        ["Dean's List",        '#166534', '#dcfce7', 'bi-trophy-fill'],
        ['Good Standing',      '#1e40af', '#dbeafe', 'bi-check-circle-fill'],
        ['Academic Warning',   '#c2410c', '#ffedd5', 'bi-exclamation-triangle-fill'],
        ['Academic Probation', '#991b1b', '#fee2e2', 'bi-x-circle-fill'],
        ['Critical',           '#7f1d1d', '#fef2f2', 'bi-x-octagon-fill'],
    ] as [$label, $color, $bg, $icon])
    <div style="background:{{ $bg }}; border-radius:10px; padding:14px; text-align:center;">
        <i class="bi {{ $icon }}" style="font-size:20px; color:{{ $color }};"></i>
        <div style="font-size:22px; font-weight:700; color:{{ $color }}; margin:4px 0;">{{ $summary[$label] }}</div>
        <div style="font-size:11px; color:{{ $color }};">{{ $label }}</div>
    </div>
    @endforeach
</div>

{{-- Filter --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-body" style="padding:12px 20px;">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            @if($batches->count() > 0)
            <select name="batch" class="form-select-rupp" style="min-width:140px;" onchange="this.form.submit()">
                <option value="">All Batches</option>
                @foreach($batches as $b)
                <option value="{{ $b }}" {{ request('batch') == $b ? 'selected' : '' }}>Batch {{ $b }}</option>
                @endforeach
            </select>
            @endif
            <select name="year_level" class="form-select-rupp" style="min-width:130px;" onchange="this.form.submit()">
                <option value="">All Years</option>
                @foreach($years as $y)
                <option value="{{ $y }}" {{ request('year_level') == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                @endforeach
            </select>
            @if(request('batch') || request('year_level'))
            <a href="{{ route('admin.standing.program', $program) }}" class="btn-rupp-outline" style="padding:7px 14px;">Clear</a>
            @endif
        </form>
    </div>
</div>

{{-- Students grouped by batch --}}
@forelse($byBatch as $batch => $batchStudents)
@php
    $batchLabel = $batch > 0 ? "Batch {$batch}" : 'No Batch Assigned';
@endphp
<div class="card-rupp" style="margin-bottom:16px;">

    {{-- Batch header - clickable --}}
    <div onclick="toggleBatch('batch-{{ $batch }}')" style="cursor:pointer; user-select:none;">
        <div style="padding:14px 20px; display:flex; justify-content:space-between; align-items:center; background:#f9fafb; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="background:var(--rupp-green); color:var(--rupp-gold); border-radius:8px; padding:6px 14px; font-size:13px; font-weight:700;">
                    {{ $batchLabel }}
                </div>
                <span style="font-size:13px; color:#6b7280;">{{ $batchStudents->count() }} students</span>
            </div>

            {{-- Mini standing summary --}}
            <div style="display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
                @php
                    $batchSummary = [
                        "Dean's List"   => $batchStudents->where('standing.label', "Dean's List")->count(),
                        'Good Standing' => $batchStudents->where('standing.label', 'Good Standing')->count(),
                        'Warning'       => $batchStudents->where('standing.label', 'Academic Warning')->count(),
                        'Probation'     => $batchStudents->where('standing.label', 'Academic Probation')->count(),
                        'Critical'      => $batchStudents->where('standing.label', 'Critical')->count(),
                    ];
                    $avgGpa = $batchStudents->count() > 0 ? round($batchStudents->avg('gpa'), 2) : 0;
                @endphp
                @foreach([
                    ["Dean's List",'#166534','#dcfce7'],
                    ['Good Standing','#1e40af','#dbeafe'],
                    ['Warning','#c2410c','#ffedd5'],
                    ['Probation','#991b1b','#fee2e2'],
                    ['Critical','#7f1d1d','#fef2f2'],
                ] as [$key, $color, $bg])
                @if($batchSummary[$key] > 0)
                <span style="background:{{ $bg }}; color:{{ $color }}; border-radius:6px; padding:3px 10px; font-size:11.5px; font-weight:600;">
                    {{ $batchSummary[$key] }} {{ $key }}
                </span>
                @endif
                @endforeach
                <span style="font-size:12px; color:#9ca3af; margin-left:4px;">Avg GPA: <strong>{{ $avgGpa }}</strong></span>
                <i class="bi bi-chevron-down expand-icon-{{ $batch }}" style="font-size:16px; color:#9ca3af; transition:transform .2s; margin-left:4px;"></i>
            </div>
        </div>
    </div>

    {{-- Expandable student table --}}
    <div id="batch-{{ $batch }}" style="display:none;">
        <div style="overflow-x:auto;">
            <table class="table-rupp">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Class Group</th>
                        <th style="text-align:center;">Year</th>
                        <th style="text-align:center;">GPA</th>
                        <th style="text-align:center;">Credits</th>
                        <th>Standing</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batchStudents->values() as $i => $row)
                    <tr>
                        <td style="color:#9ca3af; font-size:12px;">{{ $i + 1 }}</td>
                        <td>
                            <div style="font-weight:500;">{{ $row['student']->user->name }}</div>
                            @if($row['student']->user->name_kh)
                            <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $row['student']->user->name_kh }}</div>
                            @endif
                            <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $row['student']->student_id }}</div>
                        </td>
                        <td>
                            @if($row['student']->classGroup)
                            <span class="badge-rupp badge-green">{{ $row['student']->classGroup->name }}</span>
                            @else
                            <span style="color:#9ca3af; font-size:12px;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <span class="badge-rupp badge-blue">Year {{ $row['student']->year_level }}</span>
                        </td>
                        <td style="text-align:center;">
                            <div style="font-size:17px; font-weight:700; color:{{ $row['standing']['color'] }};">
                                {{ number_format($row['gpa'], 2) }}
                            </div>
                            <div style="font-size:10px; color:#9ca3af;">/ 4.00</div>
                        </td>
                        <td style="text-align:center; color:#6b7280;">{{ $row['credits'] }}</td>
                        <td>
                            <span style="background:{{ $row['standing']['bg'] }}; color:{{ $row['standing']['color'] }}; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:5px;">
                                <i class="bi {{ $row['standing']['icon'] }}" style="font-size:11px;"></i>
                                {{ $row['standing']['label'] }}
                            </span>
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
        <div style="font-size:14px; font-weight:500;">No active students found for {{ $program->name }}.</div>
    </div>
</div>
@endforelse
@endsection

@push('scripts')
<script>
function toggleBatch(id) {
    const panel = document.getElementById(id);
    const batch = id.replace('batch-', '');
    const icon  = document.querySelector('.expand-icon-' + batch);
    if (!panel) return;
    const open = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : 'block';
    if (icon) icon.style.transform = open ? '' : 'rotate(180deg)';
}
</script>
@endpush