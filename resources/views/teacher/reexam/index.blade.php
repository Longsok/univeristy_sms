{{-- resources/views/teacher/reexam/index.blade.php --}}
@extends('layouts.teacher')
@section('title', 'Re-Exam Management')
@section('page-title', 'Re-Exam Management')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $section->course->name }} — Re-Exam</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('teacher.courses.index') }}">Courses</a> /
            <a href="{{ route('teacher.grades.index', $section) }}">Grades</a> /
            Re-Exam
        </div>
    </div>
    <a href="{{ route('teacher.grades.index', $section) }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> Back to Grades
    </a>
</div>

{{-- Info banner --}}
<div style="background:#fff7ed; border:1px solid #fdba74; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:flex-start; gap:12px;">
    <i class="bi bi-info-circle-fill" style="color:#f59e0b; font-size:18px; flex-shrink:0; margin-top:1px;"></i>
    <div>
        <div style="font-size:13.5px; font-weight:600; color:#c2410c;">Re-Exam Policy</div>
        <div style="font-size:13px; color:#6b7280; margin-top:3px; line-height:1.6;">
            Students with a final grade between <strong>45–49</strong> are eligible for re-exam.
            Enter their re-exam scores below. The system will recalculate their final grade automatically.
            If they score ≥50 after re-exam, their status changes to <strong>Pass</strong>.
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert-success-rupp" style="margin-bottom:16px;">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

@if($reexamEnrollments->count() > 0)

<form method="POST" action="{{ route('teacher.reexam.save', $section) }}">
@csrf

<div class="card-rupp">
    <div class="card-rupp-header">
        <h5>
            <i class="bi bi-arrow-repeat" style="color:var(--rupp-gold)"></i>
            Students Eligible for Re-Exam
            <span class="badge-rupp badge-orange" style="margin-left:6px;">
                {{ $reexamEnrollments->count() }} students
            </span>
        </h5>
    </div>

    <div style="overflow-x:auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th style="min-width:180px;">Student</th>
                    <th style="text-align:center;">Original Grade</th>
                    <th style="text-align:center;">Status</th>
                    @foreach($section->gradeComponents as $component)
                    <th style="text-align:center; min-width:130px;">
                        {{ $component->name }}
                        <div style="font-size:10px; color:#9ca3af; font-weight:400; text-transform:none; letter-spacing:0;">
                            Re-exam score (0–{{ $component->weight_percent }})
                        </div>
                    </th>
                    @endforeach
                    <th style="text-align:center; min-width:100px; background:#fff7ed; color:#c2410c;">
                        New Final
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($reexamEnrollments as $i => $enrollment)
                <tr>
                    {{-- Student info --}}
                    <td>
                        <div style="font-weight:500; font-size:13px;">{{ $enrollment->student->user->name }}</div>
                        @if($enrollment->student->user->name_kh)
                        <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">
                            {{ $enrollment->student->user->name_kh }}
                        </div>
                        @endif
                        <div style="font-size:11.5px; color:#9ca3af; font-family:monospace;">
                            {{ $enrollment->student->student_id }}
                        </div>
                    </td>

                    {{-- Original grade --}}
                    <td style="text-align:center;">
                        <div style="font-size:18px; font-weight:700; color:#c2410c;">
                            {{ number_format($enrollment->final_grade, 1) }}
                        </div>
                        <div style="font-size:11px; color:#9ca3af;">/ 100</div>
                    </td>

                    {{-- Status --}}
                    <td style="text-align:center;">
                        <span class="badge-rupp badge-orange">
                            <i class="bi bi-arrow-repeat"></i> Re-Exam
                        </span>
                    </td>

                    {{-- Re-exam score inputs per component --}}
                    @foreach($section->gradeComponents as $j => $component)
                    @php
                        $grade = $enrollment->grades->firstWhere('grade_component_id', $component->id);
                    @endphp
                    <td style="text-align:center; padding:8px 6px;">
                        <input type="hidden"
                            name="reexam[{{ $i * $section->gradeComponents->count() + $j }}][enrollment_id]"
                            value="{{ $enrollment->id }}">
                        <input type="hidden"
                            name="reexam[{{ $i * $section->gradeComponents->count() + $j }}][grade_component_id]"
                            value="{{ $component->id }}">

                        {{-- Show original score as reference --}}
                        @if($grade)
                        <div style="font-size:10px; color:#9ca3af; margin-bottom:3px;">
                            Original: {{ number_format($grade->score, 1) }}
                        </div>
                        @endif

                        <input
                            type="number"
                            name="reexam[{{ $i * $section->gradeComponents->count() + $j }}][reexam_score]"
                            value="{{ $grade?->reexam_score }}"
                            min="0"
                            max="{{ $component->weight_percent }}"
                            step="0.5"
                            placeholder="—"
                            style="width:80px; padding:6px 8px; border:1px solid #fdba74; border-radius:6px; font-size:13px; text-align:center; outline:none; font-family:'Inter',sans-serif; background:#fff7ed;"
                            onfocus="this.style.borderColor='#f59e0b'; this.style.background='#fffbeb'"
                            onblur="this.style.borderColor='#fdba74'; this.style.background='#fff7ed'"
                            onchange="recalcReexam({{ $i }})"
                            data-weight="{{ $component->weight_percent }}"
                            data-original="{{ $grade?->score ?? 0 }}"
                            data-row="{{ $i }}">
                    </td>
                    @endforeach

                    {{-- Live new final grade --}}
                    <td style="text-align:center; background:#fffbeb;">
                        <span id="reexam-final-{{ $i }}"
                            style="font-size:16px; font-weight:700; color:#c2410c;">
                            @php
                                $newTotal = 0;
                                foreach($section->gradeComponents as $comp) {
                                    $g = $enrollment->grades->firstWhere('grade_component_id', $comp->id);
                                    if ($g) $newTotal += ($g->reexam_score ?? $g->score);
                                }
                            @endphp
                            {{ number_format($newTotal, 1) }}
                        </span>
                        <div style="font-size:10px; color:#9ca3af;">/ 100</div>
                        <div id="reexam-status-{{ $i }}" style="margin-top:4px;">
                            @if($newTotal >= 50)
                                <span class="badge-rupp badge-green" style="font-size:10px;">Will Pass</span>
                            @elseif($newTotal >= 45)
                                <span class="badge-rupp badge-orange" style="font-size:10px;">Still Re-exam</span>
                            @else
                                <span class="badge-rupp badge-red" style="font-size:10px;">Will Fail</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="padding:16px 20px; border-top:1px solid #f3f4f6; display:flex; gap:12px; align-items:center;">
        <button type="submit" class="btn-rupp-primary">
            <i class="bi bi-floppy-fill"></i> Save Re-Exam Scores & Recalculate
        </button>
        <span style="font-size:12px; color:#9ca3af;">
            <i class="bi bi-info-circle"></i>
            Grades will be automatically recalculated after saving.
        </span>
    </div>
</div>

</form>

@else
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px;">
        <i class="bi bi-check-circle-fill" style="font-size:40px; color:#16a34a; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:600; color:#166534;">No Re-Exam Students</div>
        <div style="font-size:13px; color:#9ca3af; margin-top:4px;">
            No students in this section are eligible for re-exam.
            @if(!$section->enrollments()->where('grades_finalised', true)->exists())
                <br>Make sure grades have been finalised first.
            @endif
        </div>
        <a href="{{ route('teacher.grades.index', $section) }}" class="btn-rupp-primary" style="margin-top:16px; display:inline-flex;">
            <i class="bi bi-pencil-square"></i> Go to Grade Entry
        </a>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function recalcReexam(rowIndex) {
    const inputs   = document.querySelectorAll(`input[data-row="${rowIndex}"]`);
    let newTotal   = 0;

    inputs.forEach(input => {
        const reexamVal  = parseFloat(input.value) || 0;
        const original   = parseFloat(input.dataset.original) || 0;
        const maxWeight  = parseFloat(input.dataset.weight) || 0;

        // Use reexam score if entered, otherwise original
        const effectiveScore = input.value !== '' ? Math.min(reexamVal, maxWeight) : original;
        newTotal += effectiveScore;
    });

    // Update display
    const finalEl  = document.getElementById(`reexam-final-${rowIndex}`);
    const statusEl = document.getElementById(`reexam-status-${rowIndex}`);

    if (finalEl) finalEl.textContent = newTotal.toFixed(1);

    if (statusEl) {
        if (newTotal >= 50) {
            finalEl.style.color = '#166534';
            statusEl.innerHTML  = '<span class="badge-rupp badge-green" style="font-size:10px;">Will Pass ✓</span>';
        } else if (newTotal >= 45) {
            finalEl.style.color = '#c2410c';
            statusEl.innerHTML  = '<span class="badge-rupp badge-orange" style="font-size:10px;">Still Re-exam</span>';
        } else {
            finalEl.style.color = '#991b1b';
            statusEl.innerHTML  = '<span class="badge-rupp badge-red" style="font-size:10px;">Will Fail ✗</span>';
        }
    }
}
</script>
@endpush