@extends('layouts.admin')
@section('title', 'Edit Section')
@section('page-title', 'Edit Section')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Section</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.sections.index') }}">Sections</a> / Edit
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- Section details --}}
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-collection"></i>
            <h5>Section Details</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.sections.update', $section) }}">
                @csrf @method('PUT')

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Course <span style="color:#ef4444">*</span></label>
                    <select name="course_id" class="form-select-rupp" required>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}"
                                {{ $section->course_id == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} — {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Teacher</label>
                    <select name="teacher_id" class="form-select-rupp">
                        <option value="">— No teacher assigned —</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}"
                                {{ $section->teacher_id == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name }}
                                ({{ $teacher->department->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                    <div>
                        <label class="form-label-rupp">Section Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name"
                            value="{{ old('name', $section->name) }}"
                            class="form-control-rupp"
                            placeholder="e.g. 3CS-A"
                            required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Max Students <span style="color:#ef4444">*</span></label>
                        <input type="number" name="max_students"
                            value="{{ old('max_students', $section->max_students) }}"
                            class="form-control-rupp"
                            min="1" max="200" required>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-floppy-fill"></i> Update Section
                    </button>
                    <a href="{{ route('admin.sections.index') }}" class="btn-rupp-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Grade Components --}}
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-sliders"></i>
            <h5>Grade Components</h5>
        </div>
        <div class="card-rupp-body">

            {{-- Weight status --}}
            @php
                $totalWeight = $section->gradeComponents->sum('weight_percent');
            @endphp

            @if($section->gradeComponents->count() > 0 && $totalWeight == 100)
            <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:12.5px; color:#166534; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-check-circle-fill"></i>
                Weights complete — total 100%
            </div>
            @elseif($section->gradeComponents->count() > 0 && $totalWeight != 100)
            <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:12.5px; color:#991b1b; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Total weight is {{ $totalWeight }}% — must equal 100%
                ({{ 100 - $totalWeight }}% remaining)
            </div>
            @else
            <div style="background:#fff7ed; border:1px solid #fdba74; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:12.5px; color:#c2410c; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-info-circle-fill"></i>
                No grade components yet. Add components below (must total 100%).
            </div>
            @endif

            {{-- Existing components --}}
            @forelse($section->gradeComponents->sortBy('sort_order') as $component)
            <div style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f3f4f6;">
                <div style="flex:1;">
                    <div style="font-size:13.5px; font-weight:500; color:#111827;">
                        {{ $component->name }}
                        @if($component->is_reexam_component)
                            <span class="badge-rupp badge-orange" style="font-size:10px; margin-left:4px;">Re-exam</span>
                        @endif
                    </div>
                    <div style="font-size:11.5px; color:#9ca3af;">Max score: {{ $component->max_score }}</div>
                </div>
                <span class="badge-rupp badge-green" style="font-size:12px;">
                    {{ $component->weight_percent }}%
                </span>
                <form action="{{ route('admin.grade-components.destroy', $component) }}" method="POST"
                    onsubmit="return confirm('Delete {{ $component->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon delete" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            </div>
            @empty
            <div style="text-align:center; padding:20px; color:#9ca3af; font-size:13px;">
                No components yet. Add below.
            </div>
            @endforelse

            {{-- Add new component --}}
            <div style="margin-top:16px; padding-top:16px; border-top:1px solid #f3f4f6;">
                <div style="font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">
                    Add Component
                </div>

                @if(session('success'))
                <div class="alert-success-rupp" style="margin-bottom:12px;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert-error-rupp" style="margin-bottom:12px; font-size:12.5px;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('admin.grade-components.store') }}">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $section->id }}">

                    <div style="margin-bottom:10px;">
                        <label class="form-label-rupp">Component Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" class="form-control-rupp"
                            placeholder="e.g. Assignment, Midterm, Final Exam"
                            value="{{ old('name') }}" required>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                        <div>
                            <label class="form-label-rupp">Weight (%) <span style="color:#ef4444">*</span></label>
                            <input type="number" name="weight_percent" class="form-control-rupp"
                                placeholder="e.g. 30"
                                value="{{ old('weight_percent') }}"
                                min="1" max="100" required>
                            @error('weight_percent')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            @if($totalWeight < 100)
                            <div style="font-size:11px; color:#9ca3af; margin-top:3px;">
                                Remaining: {{ 100 - $totalWeight }}%
                            </div>
                            @endif
                        </div>
                        <div>
                            <label class="form-label-rupp">Max Score <span style="color:#ef4444">*</span></label>
                            <input type="number" name="max_score" class="form-control-rupp"
                                placeholder="e.g. 100"
                                value="{{ old('max_score', 100) }}"
                                min="1" required>
                        </div>
                    </div>

                    {{-- Quick presets --}}
                    <div style="margin-bottom:12px;">
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:6px;">Quick presets:</div>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <button type="button" onclick="fillComponent('Assignment', 20, 100)"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">
                                Assignment 20%
                            </button>
                            <button type="button" onclick="fillComponent('Midterm', 30, 100)"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">
                                Midterm 30%
                            </button>
                            <button type="button" onclick="fillComponent('Final Exam', 50, 100)"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">
                                Final 50%
                            </button>
                            <button type="button" onclick="fillComponent('Attendance', 10, 10)"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">
                                Attendance 10%
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-rupp-primary" style="width:100%; justify-content:center;">
                        <i class="bi bi-plus-lg"></i> Add Component
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function fillComponent(name, weight, maxScore) {
    document.querySelector('input[name="name"]').value           = name;
    document.querySelector('input[name="weight_percent"]').value = weight;
    document.querySelector('input[name="max_score"]').value      = maxScore;
}
</script>
@endpush