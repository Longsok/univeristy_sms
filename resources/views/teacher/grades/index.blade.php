@extends('layouts.teacher')
@section('title', 'Enter Grades')
@section('page-title', 'Grade Entry')

@section('content')
@php
    $isLocked = $section->enrollments()
        ->where('status', 'enrolled')
        ->where('grades_finalised', true)
        ->exists();
@endphp

<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $section->course->name }}</h1>
        <div class="breadcrumb-text">
            {{ $section->course->code }} — {{ $section->name }} / Grade Entry
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('teacher.reports.index', $section) }}" class="btn-rupp-outline">
            <i class="bi bi-bar-chart-line"></i> View Report
        </a>
        @if(!$isLocked)
        <button type="button" class="btn-rupp-outline" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="bi bi-upload"></i> Import Excel
        </button>
        {{-- Sync attendance button --}}
        <form method="POST" action="{{ route('teacher.grades.sync-attendance', $section) }}">
            @csrf
            <button type="submit" class="btn-rupp-outline"
                onclick="return confirm('Sync attendance scores into the Attendance grade component?')"
                title="Auto-fill attendance scores from attendance records">
                <i class="bi bi-arrow-repeat" style="color:#f59e0b;"></i> Sync Attendance
            </button>
        </form>
        @endif
    </div>
</div>

{{-- LOCKED BANNER --}}
@if($isLocked)
<div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; align-items:center; gap:10px;">
        <i class="bi bi-lock-fill" style="color:#dc2626; font-size:20px;"></i>
        <div>
            <div style="font-size:14px; font-weight:600; color:#991b1b;">Grades Finalised & Locked</div>
            <div style="font-size:12.5px; color:#6b7280; margin-top:2px;">
                These grades have been finalised. Contact the administrator to unlock if changes are needed.
            </div>
        </div>
    </div>
    <a href="{{ route('teacher.reports.index', $section) }}" class="btn-rupp-outline" style="font-size:13px;">
        <i class="bi bi-bar-chart-line"></i> View Report
    </a>
</div>
@endif

{{-- Grading scheme --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-header">
        <h5><i class="bi bi-sliders" style="color:var(--rupp-gold)"></i> Grading Scheme</h5>
        @if($section->isGradingConfigured())
            <span class="badge-rupp badge-green"><i class="bi bi-check-circle-fill"></i> Weights sum to 100%</span>
        @else
            <span class="badge-rupp badge-red"><i class="bi bi-exclamation-triangle-fill"></i> Weights incomplete</span>
        @endif
    </div>
    <div class="card-rupp-body" style="padding:14px 20px;">
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            @foreach($components as $component)
            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px 16px; min-width:130px; text-align:center;">
                <div style="font-size:12px; color:#6b7280;">{{ $component->name }}</div>
                <div style="font-size:20px; font-weight:700; color:var(--rupp-green); line-height:1; margin-top:4px;">
                    {{ $component->weight_percent }}
                </div>
                <div style="font-size:10px; color:#9ca3af; margin-top:2px;">points max</div>
            </div>
            @if(!$loop->last)
            <div style="font-size:18px; color:#d1d5db; font-weight:300;">+</div>
            @endif
            @endforeach
            <div style="font-size:18px; color:#d1d5db; font-weight:300;">=</div>
            <div style="background:var(--rupp-green); border-radius:8px; padding:10px 16px; min-width:100px; text-align:center;">
                <div style="font-size:12px; color:rgba(255,255,255,0.6);">Total</div>
                <div style="font-size:20px; font-weight:700; color:var(--rupp-gold); line-height:1; margin-top:4px;">100</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.5); margin-top:2px;">points max</div>
            </div>
        </div>
        @if(!$isLocked)
        <div style="margin-top:12px; padding:10px 14px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; font-size:12.5px; color:#166534; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-info-circle-fill"></i>
            Enter scores directly as points. If Midterm is worth <strong>30 points</strong>, enter a number between <strong>0–30</strong>. All scores add up to <strong>100</strong>.
        </div>
        @endif
    </div>
</div>

{{-- Grade entry table --}}
<div class="card-rupp">
    <div class="card-rupp-header">
        <h5>
            <i class="bi bi-{{ $isLocked ? 'lock-fill' : 'pencil-square' }}" style="color:var(--rupp-gold)"></i>
            Student Grades
            @if($isLocked)
            <span class="badge-rupp badge-red" style="margin-left:6px; font-size:10px;">
                <i class="bi bi-lock-fill"></i> Locked
            </span>
            @endif
        </h5>
    </div>

    <form method="POST" action="{{ route('teacher.grades.upsert', $section) }}">
        @csrf
        <div style="overflow-x:auto;">
            <table class="table-rupp">
                <thead>
                    <tr>
                        <th style="min-width:180px; position:sticky; left:0; background:#f9fafb; z-index:5;">Student</th>
                        @foreach($components as $component)
                        <th style="min-width:110px; text-align:center;">
                            {{ $component->name }}
                            <div style="font-size:10px; color:#9ca3af; font-weight:400; text-transform:none; letter-spacing:0;">
                                (0 – {{ $component->weight_percent }})
                            </div>
                        </th>
                        @endforeach
                        <th style="text-align:center; min-width:80px; background:#f0fdf4; color:var(--rupp-green);">
                            Final
                            <div style="font-size:10px; font-weight:400; text-transform:none; letter-spacing:0; color:#9ca3af;">(/ 100)</div>
                        </th>
                        <th style="min-width:100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollments as $i => $enrollment)
                    <tr style="{{ $isLocked ? 'background:#fafafa;' : '' }}">
                        <td style="position:sticky; left:0; background:{{ $isLocked ? '#fafafa' : '#fff' }}; z-index:4;">
                            <div style="font-weight:500; font-size:13px;">{{ $enrollment->student->user->name }}</div>
                            @if($enrollment->student->user->name_kh)
                            <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $enrollment->student->user->name_kh }}</div>
                            @endif
                            <div style="font-size:11.5px; color:#9ca3af; font-family:monospace;">{{ $enrollment->student->student_id }}</div>
                        </td>

                        @foreach($components as $j => $component)
                        @php $grade = $enrollment->grades->firstWhere('grade_component_id', $component->id); @endphp
                        <td style="text-align:center; padding:8px 6px;">
                            <input type="hidden" name="grades[{{ $i * $components->count() + $j }}][enrollment_id]" value="{{ $enrollment->id }}">
                            <input type="hidden" name="grades[{{ $i * $components->count() + $j }}][grade_component_id]" value="{{ $component->id }}">
                            <input
                                type="number"
                                name="grades[{{ $i * $components->count() + $j }}][score]"
                                value="{{ $grade?->score }}"
                                min="0"
                                max="{{ $component->weight_percent }}"
                                step="0.5"
                                {{ $isLocked ? 'readonly disabled' : '' }}
                                style="width:80px; padding:6px 8px; border:1px solid {{ $isLocked ? '#e5e7eb' : '#d1d5db' }}; border-radius:6px; font-size:13px; text-align:center; outline:none; font-family:'Inter',sans-serif; background:{{ $isLocked ? '#f3f4f6' : '#fff' }}; color:{{ $isLocked ? '#6b7280' : '#111827' }}; cursor:{{ $isLocked ? 'not-allowed' : 'text' }};"
                                @if(!$isLocked)
                                onfocus="this.style.borderColor='var(--rupp-green)'"
                                onblur="this.style.borderColor='#d1d5db'"
                                onchange="recalcFinal({{ $i }})"
                                @endif
                                data-weight="{{ $component->weight_percent }}"
                                data-row="{{ $i }}">
                        </td>
                        @endforeach

                        {{-- Final grade --}}
                        <td style="text-align:center; background:#f9fafb;">
                            @php
                                $liveFinal = 0;
                                foreach($components as $comp) {
                                    $g = $enrollment->grades->firstWhere('grade_component_id', $comp->id);
                                    if ($g) $liveFinal += $g->score;
                                }
                            @endphp
                            <span id="final-{{ $i }}"
                                style="font-weight:700; font-size:14px; color:{{ $liveFinal >= 50 ? 'var(--rupp-green)' : ($liveFinal >= 45 ? '#c2410c' : '#dc2626') }};">
                                {{ $liveFinal > 0 ? number_format($liveFinal, 1) : '—' }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($enrollment->grade_status && $enrollment->grade_status !== 'not_graded')
                                <span class="badge-rupp {{ match($enrollment->grade_status) {
                                    'pass'       => 'badge-green',
                                    'reexam'     => 'badge-orange',
                                    'fail'       => 'badge-red',
                                    'incomplete' => 'badge-gray',
                                    default      => 'badge-gray'
                                } }}">
                                    {{ ucfirst($enrollment->grade_status) }}
                                </span>
                                @if($enrollment->letter_grade)
                                    <span class="badge-rupp badge-blue" style="margin-left:4px;">{{ $enrollment->letter_grade }}</span>
                                @endif
                            @else
                                <span class="badge-rupp badge-gray">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="padding:16px 20px; border-top:1px solid #f3f4f6; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            @if(!$isLocked)
            <button type="submit" class="btn-rupp-primary">
                <i class="bi bi-floppy-fill"></i> Save All Grades
            </button>
            @php $reexamCount = $section->enrollments()->where('grade_status','reexam')->count(); @endphp
            @if($reexamCount > 0)
            <a href="{{ route('teacher.reexam.index', $section) }}" class="btn-rupp-outline" style="position:relative;">
                <i class="bi bi-arrow-repeat" style="color:#f59e0b;"></i>
                Re-Exam ({{ $reexamCount }} students)
            </a>
            @endif
            @if($section->isGradingConfigured())
            <button type="button" class="btn-rupp-gold"
                onclick="if(confirm('Finalise grades? This will lock all grades and cannot be undone without admin help.')) { document.getElementById('finaliseForm').submit(); }">
                <i class="bi bi-lock-fill"></i> Finalise & Lock Grades
            </button>
            @endif
            <span style="font-size:12px; color:#9ca3af;">
                <i class="bi bi-info-circle"></i>
                Save first, then finalise to lock grades and generate report.
            </span>
            @else
            <div style="display:flex; align-items:center; gap:8px; color:#991b1b; font-size:13px;">
                <i class="bi bi-lock-fill"></i>
                Grades are locked. Contact admin to unlock.
            </div>
            @endif
        </div>
    </form>

    <form id="finaliseForm" method="POST" action="{{ route('teacher.grades.finalise', $section) }}" style="display:none;">
        @csrf
    </form>
</div>

{{-- Import modal (only if not locked) --}}
@if(!$isLocked)
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none;">
            <div class="rupp-header-strip">
                <i class="bi bi-upload"></i>
                <h5>Import Grades from Excel</h5>
            </div>
            <div style="padding:24px;">
                <p style="font-size:13px; color:#6b7280; margin-bottom:16px;">
                    Upload an Excel or CSV file with columns:
                    <code>student_id</code>, <code>component_name</code>, <code>score</code>
                </p>
                <form method="POST" action="{{ route('teacher.grades.import', $section) }}" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom:16px;">
                        <label class="form-label-rupp">Select File</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control-rupp">
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-rupp-primary"><i class="bi bi-upload"></i> Import</button>
                        <button type="button" class="btn-rupp-outline" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function recalcFinal(rowIndex) {
    const inputs = document.querySelectorAll(`input[data-row="${rowIndex}"]`);
    let total = 0;
    inputs.forEach(input => {
        const val = parseFloat(input.value) || 0;
        const max = parseFloat(input.dataset.weight) || 0;
        if (val > max) { input.value = max; total += max; }
        else total += val;
    });
    const el = document.getElementById(`final-${rowIndex}`);
    if (el) {
        el.textContent = total > 0 ? total.toFixed(1) : '—';
        el.style.color = total >= 50 ? 'var(--rupp-green)' : (total >= 45 ? '#c2410c' : '#dc2626');
    }
}
</script>
@endpush