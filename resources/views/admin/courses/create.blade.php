@extends('layouts.admin')
@section('title', 'Add Course')
@section('page-title', 'Add Course')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Add New Course</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.courses.index') }}">Courses</a> / Add
        </div>
    </div>
</div>

<div style="max-width:680px;">
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-book"></i>
            <h5>Course Details</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.courses.store') }}">
                @csrf

                {{-- Program (auto-fills department) --}}
                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Program <span style="color:#ef4444">*</span></label>
                    <select name="program_id" id="programSelect" class="form-select-rupp" required
                        onchange="fillDepartment(this)">
                        <option value="">— Select Program —</option>
                        @foreach($programs->groupBy(fn($p) => $p->department->faculty->name) as $facultyName => $facultyPrograms)
                            <optgroup label="{{ $facultyName }}">
                                @foreach($facultyPrograms as $program)
                                    <option value="{{ $program->id }}"
                                        data-department-id="{{ $program->department_id }}"
                                        data-department-name="{{ $program->department->name }}"
                                        {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }} ({{ $program->code }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('program_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Department (hidden, auto-filled from program) --}}
                <input type="hidden" name="department_id" id="departmentId" value="{{ old('department_id') }}">

                {{-- Department display (read-only) --}}
                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Department</label>
                    <input type="text" id="departmentDisplay" class="form-control-rupp"
                        placeholder="Auto-filled from program"
                        style="background:#f9fafb;color:#6b7280;" readonly>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    {{-- Semester --}}
                    <div>
                        <label class="form-label-rupp">Semester <span style="color:#ef4444">*</span></label>
                        <select name="semester_id" id="semesterSelect" class="form-select-rupp" required>
                            <option value="">— Select Semester —</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}"
                                    data-year="{{ $sem->year_level ?? 'all' }}"
                                    {{ old('semester_id') == $sem->id ? 'selected' : '' }}>
                                    {{ $sem->name }} {{ $sem->academic_year }}
                                    @if($sem->year_level) (Year {{ $sem->year_level }}) @else (All Years) @endif
                                    @if($sem->isRunning()) ● Running @endif
                                </option>
                            @endforeach
                        </select>
                        <div style="font-size:11px; color:#9ca3af; margin-top:3px;">
                            Showing semesters for selected year level. Running semesters are marked with ●
                        </div>
                        @error('semester_id')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    {{-- Year Level --}}
                    <div>
                        <label class="form-label-rupp">Year Level <span style="color:#ef4444">*</span></label>
                        <select name="year_level" class="form-select-rupp" required>
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}" {{ old('year_level') == $i ? 'selected' : '' }}>
                                    Year {{ $i }}
                                </option>
                            @endfor
                        </select>
                        @error('year_level')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    {{-- Course Code --}}
                    <div>
                        <label class="form-label-rupp">Course Code <span style="color:#ef4444">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="form-control-rupp" placeholder="e.g. CS301" required>
                        @error('code')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    {{-- Credit Units --}}
                    <div>
                        <label class="form-label-rupp">Credit Units <span style="color:#ef4444">*</span></label>
                        <input type="number" name="credit_units" value="{{ old('credit_units', 3) }}"
                            class="form-control-rupp" min="1" max="6" required>
                        @error('credit_units')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Course Name --}}
                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Course Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control-rupp" placeholder="e.g. Data Structures & Algorithms" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Description --}}
                <div style="margin-bottom:20px;">
                    <label class="form-label-rupp">Description</label>
                    <textarea name="description" rows="3" class="form-control-rupp"
                        placeholder="Optional course description">{{ old('description') }}</textarea>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-check-lg"></i> Create Course
                    </button>
                    <a href="{{ route('admin.courses.index') }}" class="btn-rupp-outline">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Filter semester dropdown based on selected year level
document.querySelector('select[name="year_level"]')?.addEventListener('change', function() {
    const yearLevel = this.value;
    const semSelect = document.getElementById('semesterSelect');
    if (!semSelect) return;

    Array.from(semSelect.options).forEach(opt => {
        if (!opt.value) return; // keep placeholder
        const optYear = opt.dataset.year;
        // Show if matches year level OR is 'all'
        opt.style.display = (optYear === yearLevel || optYear === 'all') ? '' : 'none';
    });

    // Reset selection
    semSelect.value = '';
});

// Auto-fill department when program is selected
function fillDepartment(select) {
    const option = select.options[select.selectedIndex];
    const deptId   = option.getAttribute('data-department-id') || '';
    const deptName = option.getAttribute('data-department-name') || '';

    document.getElementById('departmentId').value      = deptId;
    document.getElementById('departmentDisplay').value = deptName || 'Auto-filled from program';
}

// Run on page load if old() has a value
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('programSelect');
    if (select.value) fillDepartment(select);
});
</script>
@endpush