@extends('layouts.admin')
@section('title', 'Enrollments')
@section('page-title', 'Enrollments')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Enrollments</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Enrollments
        </div>
    </div>
</div>

{{-- Section selector --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-body" style="padding:14px 20px;">
        <form method="GET" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <label style="font-size:13px; font-weight:500; color:#374151; white-space:nowrap;">
                Select Section:
            </label>
            <select name="section_id" class="form-select-rupp" style="flex:1; min-width:280px;"
                onchange="this.form.submit()">
                <option value="">— Choose a section to view students —</option>
                @foreach($sections->groupBy(fn($s) => $s->course->code . ' — ' . $s->course->name) as $courseName => $courseSections)
                    <optgroup label="{{ $courseName }}">
                        @foreach($courseSections as $section)
                        <option value="{{ $section->id }}"
                            {{ request('section_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                            ({{ $section->teacher?->user?->name ?? 'No teacher' }})
                        </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @if(request('section_id'))
            <a href="{{ route('admin.enrollments.index') }}" class="btn-rupp-outline" style="padding:8px 14px;">
                Clear
            </a>
            @endif
        </form>
    </div>
</div>

@if($selectedSection)

{{-- Section info --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div style="background:var(--rupp-green); padding:14px 20px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                {{ $selectedSection->course->code }} — {{ $selectedSection->name }}
            </div>
            <div style="color:#fff; font-size:15px; font-weight:600; margin-top:2px;">
                {{ $selectedSection->course->name }}
            </div>
            @if($selectedSection->course->program)
            <div style="color:rgba(255,255,255,0.6); font-size:12px; margin-top:2px;">
                {{ $selectedSection->course->program->name }}
                · {{ $selectedSection->course->program->department->faculty->name }}
            </div>
            @endif
        </div>
        <div style="display:flex; gap:16px; text-align:center;">
            <div style="background:rgba(255,255,255,0.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:22px; font-weight:700; color:#fff;">{{ $enrollments->count() }}</div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">Enrolled</div>
            </div>
            <div style="background:rgba(255,255,255,0.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:22px; font-weight:700; color:var(--rupp-gold);">{{ $selectedSection->max_students }}</div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">Max</div>
            </div>
            <div style="background:rgba(255,255,255,0.1); border-radius:8px; padding:10px 16px;">
                <div style="font-size:22px; font-weight:700; color:#fff;">
                    {{ max(0, $selectedSection->max_students - $enrollments->count()) }}
                </div>
                <div style="font-size:11px; color:rgba(255,255,255,0.6);">Available</div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start;">

    {{-- Enrolled students list --}}
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5>
                <i class="bi bi-people-fill" style="color:var(--rupp-gold)"></i>
                Enrolled Students
                <span class="badge-rupp badge-green" style="margin-left:6px;">{{ $enrollments->count() }}</span>
            </h5>
        </div>

        @forelse($enrollments as $i => $enrollment)
        <div style="padding:12px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; {{ $i % 2 === 1 ? 'background:#fafafa;' : '' }}">
            <div style="display:flex; align-items:center; gap:12px;">
                {{-- Avatar --}}
                <div style="width:36px; height:36px; border-radius:50%; background:#dcfce7; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#166534; flex-shrink:0;">
                    {{ strtoupper(substr($enrollment->student->user->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:13.5px; font-weight:500; color:#111827;">
                        {{ $enrollment->student->user->name }}
                    </div>
                    @if($enrollment->student->user->name_kh)
                    <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">
                        {{ $enrollment->student->user->name_kh }}
                    </div>
                    @endif
                    <div style="font-size:11.5px; color:#9ca3af; font-family:monospace;">
                        {{ $enrollment->student->student_id }}
                        · {{ $enrollment->student->program->code }}
                        · Year {{ $enrollment->student->year_level }}
                    </div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <span class="badge-rupp {{ $enrollment->status === 'enrolled' ? 'badge-green' : 'badge-gray' }}">
                    {{ ucfirst($enrollment->status) }}
                </span>
                <form action="{{ route('admin.enrollments.destroy', $enrollment) }}" method="POST"
                    onsubmit="return confirm('Remove {{ $enrollment->student->user->name }} from this section?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon delete" title="Remove">
                        <i class="bi bi-person-dash-fill"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align:center; padding:40px; color:#9ca3af;">
            <i class="bi bi-people" style="font-size:32px; display:block; margin-bottom:10px;"></i>
            <div style="font-size:14px; font-weight:500;">No students enrolled yet.</div>
            <div style="font-size:12px; margin-top:4px;">Use the form on the right to enroll students.</div>
        </div>
        @endforelse
    </div>

    {{-- Enroll form --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Single enroll --}}
        <div class="card-rupp">
            <div class="rupp-header-strip">
                <i class="bi bi-person-plus-fill"></i>
                <h5>Enroll a Student</h5>
            </div>
            <div class="card-rupp-body">
                <form method="POST" action="{{ route('admin.enrollments.store') }}">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Student <span style="color:#ef4444">*</span></label>
                        <select name="student_id" class="form-select-rupp" required>
                            <option value="">— Select Student —</option>
                            @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->student_id }} — {{ $student->user->name }}
                                ({{ $student->program->code }}
                                @if($student->batch) · Batch {{ $student->batch }}@endif
                                @if($student->classGroup) · {{ $student->classGroup->name }}@endif)
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-rupp-primary" style="width:100%; justify-content:center;">
                        <i class="bi bi-person-plus-fill"></i> Enroll Student
                    </button>
                </form>
            </div>
        </div>

        {{-- Bulk enroll --}}
        <div class="card-rupp">
            <div class="rupp-header-strip">
                <i class="bi bi-people-fill"></i>
                <h5>Bulk Enroll</h5>
            </div>
            <div class="card-rupp-body">
                <form method="POST" action="{{ route('admin.enrollments.bulk') }}">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Select Students</label>
                        <div style="border:1px solid #d1d5db; border-radius:8px; padding:10px; max-height:220px; overflow-y:auto;">
                            @forelse($availableStudents as $student)
                            <label style="display:flex; align-items:center; gap:8px; padding:5px 0; font-size:13px; cursor:pointer; border-bottom:1px solid #f9fafb;">
                                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                    style="accent-color:var(--rupp-green);">
                                <div>
                                    <div style="font-weight:500;">{{ $student->user->name }}</div>
                                    <div style="font-size:11px; color:#9ca3af; font-family:monospace;">
                                        {{ $student->student_id }} · {{ $student->program->code }}
                                    </div>
                                </div>
                            </label>
                            @empty
                            <div style="text-align:center; color:#9ca3af; font-size:12px; padding:12px;">
                                All students are already enrolled.
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @if($availableStudents->count() > 0)
                    <div style="display:flex; gap:8px; margin-bottom:12px;">
                        <button type="button" onclick="selectAll(true)"
                            class="btn-rupp-outline" style="padding:5px 12px; font-size:12px; flex:1;">
                            Select All
                        </button>
                        <button type="button" onclick="selectAll(false)"
                            class="btn-rupp-outline" style="padding:5px 12px; font-size:12px; flex:1;">
                            Clear All
                        </button>
                    </div>
                    <button type="submit" class="btn-rupp-gold" style="width:100%; justify-content:center;">
                        <i class="bi bi-people-fill"></i> Bulk Enroll
                    </button>
                    @endif
                </form>
            </div>
        </div>

        {{-- Enroll by Class Group --}}
        @if(\App\Models\ClassGroup::count() > 0)
        <div class="card-rupp" style="margin-top:16px;">
            <div class="rupp-header-strip">
                <i class="bi bi-lightning-fill"></i>
                <h5>Enroll by Class Group</h5>
            </div>
            <div class="card-rupp-body">
                <p style="font-size:13px; color:#6b7280; margin-bottom:14px; line-height:1.6;">
                    Enroll all students from a class group (M1, M2...) into this section at once.
                </p>
                <form method="POST" action="{{ route('admin.enrollments.from-class-group') }}">
                    @csrf
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Class Group <span style="color:#ef4444">*</span></label>
                        <select name="class_group_id" class="form-select-rupp" required>
                            <option value="">— Select Class Group —</option>
                            @foreach(\App\Models\ClassGroup::with('program')->withCount('students')->where('is_active',true)->get() as $group)
                            <option value="{{ $group->id }}">
                                {{ $group->name }} — {{ $group->program->code }} Year {{ $group->year_level }}
                                ({{ $group->students_count }} students)
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-rupp-gold" style="width:100%; justify-content:center;"
                        onclick="return confirm('Enroll all students from this class group?')">
                        <i class="bi bi-lightning-fill"></i> Enroll Class Group
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

</div>

@else

{{-- No section selected --}}
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:60px;">
        <i class="bi bi-collection" style="font-size:48px; color:#d1d5db; display:block; margin-bottom:16px;"></i>
        <div style="font-size:16px; font-weight:600; color:#6b7280; margin-bottom:6px;">
            Select a section above
        </div>
        <div style="font-size:13px; color:#9ca3af;">
            Choose a section from the dropdown to view enrolled students and manage enrollments.
        </div>
    </div>
</div>

@endif

@endsection

@push('scripts')
<script>
function selectAll(check) {
    document.querySelectorAll('input[name="student_ids[]"]')
        .forEach(cb => cb.checked = check);
}
</script>
@endpush