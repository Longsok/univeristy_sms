@extends('layouts.admin')
@section('title', 'Class Group — {{ $classGroup->name }}')
@section('page-title', 'Class Group')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>
            <span style="font-size:28px; font-weight:800; color:var(--rupp-green);">{{ $classGroup->name }}</span>
            <span style="font-size:14px; color:#6b7280; margin-left:10px; font-weight:400;">
                {{ $classGroup->program->name }} · Year {{ $classGroup->year_level }}
                @if($classGroup->batch)
                · <span style="color:var(--rupp-gold); font-weight:600;">Batch {{ $classGroup->batch }}</span>
                @endif
            </span>
        </h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.class-groups.index') }}">Class Groups</a> /
            {{ $classGroup->name }}
        </div>
    </div>
    <a href="{{ route('admin.class-groups.index') }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div style="display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start;">

    {{-- Students in this group --}}
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5>
                <i class="bi bi-people-fill" style="color:var(--rupp-gold)"></i>
                Students in {{ $classGroup->name }}
                <span class="badge-rupp badge-green" style="margin-left:6px;">
                    {{ $classGroup->students->count() }} / {{ $classGroup->capacity }}
                </span>
            </h5>
        </div>

        @forelse($classGroup->students->sortBy('student_id') as $i => $student)
        <div style="padding:10px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; {{ $i % 2 === 1 ? 'background:#fafafa;' : '' }}">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:34px; height:34px; border-radius:50%; background:#dcfce7; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#166534; flex-shrink:0;">
                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:13.5px; font-weight:500; color:#111827;">{{ $student->user->name }}</div>
                    @if($student->user->name_kh)
                    <div style="font-size:11px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $student->user->name_kh }}</div>
                    @endif
                    <div style="font-size:11.5px; color:#9ca3af; font-family:monospace;">{{ $student->student_id }}</div>
                    @if($student->batch)
                    <div style="font-size:10.5px; margin-top:2px;">
                        <span style="background:#dbeafe; color:#1e40af; border-radius:4px; padding:1px 6px; font-size:10px;">
                            Batch {{ $student->batch }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            <form action="{{ route('admin.class-groups.remove-student', [$classGroup, $student]) }}" method="POST"
                onsubmit="return confirm('Remove {{ $student->user->name }} from {{ $classGroup->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-icon delete" title="Remove from group">
                    <i class="bi bi-person-dash-fill"></i>
                </button>
            </form>
        </div>
        @empty
        <div style="padding:40px; text-align:center; color:#9ca3af;">
            <i class="bi bi-people" style="font-size:32px; display:block; margin-bottom:10px;"></i>
            <div style="font-size:14px; font-weight:500;">No students in this group yet.</div>
            <div style="font-size:12px; margin-top:4px;">Add students from the panel on the right.</div>
        </div>
        @endforelse
    </div>

    {{-- Right panel --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- Enroll group into section --}}
        <div class="card-rupp">
            <div class="rupp-header-strip">
                <i class="bi bi-collection-fill"></i>
                <h5>Enroll Group into Section</h5>
            </div>
            <div class="card-rupp-body">
                <p style="font-size:13px; color:#6b7280; margin-bottom:14px; line-height:1.6;">
                    Enroll all <strong>{{ $classGroup->students->count() }} students</strong> in
                    <strong>{{ $classGroup->name }}</strong> into a section at once.
                </p>
                <form method="POST" action="{{ route('admin.class-groups.enroll', $classGroup) }}">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Select Section <span style="color:#ef4444">*</span></label>
                        <select name="section_id" class="form-select-rupp" required>
                            <option value="">— Select Section —</option>
                            @foreach(\App\Models\Section::with('course')->get()
                                ->groupBy(fn($s) => $s->course->code . ' — ' . $s->course->name)
                                as $courseName => $sections)
                                <optgroup label="{{ $courseName }}">
                                    @foreach($sections as $section)
                                    <option value="{{ $section->id }}">
                                        {{ $section->name }}
                                        ({{ $section->enrollments->where('status','enrolled')->count() }}/{{ $section->max_students }})
                                    </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-rupp-gold" style="width:100%; justify-content:center;"
                        onclick="return confirm('Enroll all {{ $classGroup->students->count() }} students into this section?')">
                        <i class="bi bi-lightning-fill"></i>
                        Enroll All {{ $classGroup->students->count() }} Students
                    </button>
                </form>
            </div>
        </div>

        {{-- Add students to group --}}
        @if($available->count() > 0)
        <div class="card-rupp">
            <div class="rupp-header-strip">
                <i class="bi bi-person-plus-fill"></i>
                <h5>Add Students to Group</h5>
            </div>
            <div class="card-rupp-body">
                <p style="font-size:12.5px; color:#6b7280; margin-bottom:12px;">
                    Students from <strong>{{ $classGroup->program->code }}</strong>
                    Year <strong>{{ $classGroup->year_level }}</strong> not yet in any group:
                </p>
                <form method="POST" action="{{ route('admin.class-groups.add-students', $classGroup) }}">
                    @csrf
                    <div style="border:1px solid #d1d5db; border-radius:8px; padding:10px; max-height:240px; overflow-y:auto; margin-bottom:12px;">
                        @foreach($available as $student)
                        <label style="display:flex; align-items:center; gap:8px; padding:5px 0; font-size:13px; cursor:pointer; border-bottom:1px solid #f9fafb;">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                style="accent-color:var(--rupp-green);">
                            <div>
                                <div style="font-weight:500;">{{ $student->user->name }}</div>
                                <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $student->student_id }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <div style="display:flex; gap:8px; margin-bottom:10px;">
                        <button type="button" onclick="selectAll(true)"
                            class="btn-rupp-outline" style="padding:5px 12px; font-size:12px; flex:1;">
                            Select All
                        </button>
                        <button type="button" onclick="selectAll(false)"
                            class="btn-rupp-outline" style="padding:5px 12px; font-size:12px; flex:1;">
                            Clear
                        </button>
                    </div>
                    <button type="submit" class="btn-rupp-primary" style="width:100%; justify-content:center;">
                        <i class="bi bi-person-plus-fill"></i> Add to {{ $classGroup->name }}
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card-rupp">
            <div class="card-rupp-body" style="text-align:center; padding:24px; color:#9ca3af; font-size:13px;">
                <i class="bi bi-check-circle-fill" style="color:#16a34a; font-size:24px; display:block; margin-bottom:8px;"></i>
                All eligible students are assigned to groups.
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
function selectAll(check) {
    document.querySelectorAll('input[name="student_ids[]"]')
        .forEach(cb => cb.checked = check);
}
</script>
@endpush