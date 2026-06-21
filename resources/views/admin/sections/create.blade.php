@extends('layouts.admin')
@section('title', 'Add Section')
@section('page-title', 'Add Section')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Add New Section</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.sections.index') }}">Sections</a> / Add
        </div>
    </div>
</div>

<div style="max-width:640px;">
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-collection"></i>
            <h5>Section Details</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.sections.store') }}">
                @csrf

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Course <span style="color:#ef4444">*</span></label>
                    <select name="course_id" class="form-select-rupp" required>
                        <option value="">— Select Course —</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} — {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Teacher</label>
                    <select name="teacher_id" class="form-select-rupp">
                        <option value="">— No teacher yet —</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name }} ({{ $teacher->department->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp">Section Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="form-control-rupp" placeholder="e.g. M1, 3CS-A" required>
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Max Students <span style="color:#ef4444">*</span></label>
                        <input type="number" name="max_students" value="{{ old('max_students', 40) }}"
                            class="form-control-rupp" min="1" max="200" required>
                    </div>
                </div>

                {{-- Class Group enrollment --}}
                @if($classGroups->count() > 0)
                <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:16px; margin-bottom:16px;">
                    <div style="font-size:13px; font-weight:600; color:#166534; margin-bottom:10px; display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-lightning-fill"></i>
                        Auto-Enroll from Class Group (optional)
                    </div>
                    <div style="font-size:12.5px; color:#6b7280; margin-bottom:10px;">
                        Select a class group to automatically enroll all its students when this section is created.
                    </div>
                    <select name="class_group_id" class="form-select-rupp">
                        <option value="">— No auto-enroll —</option>
                        @foreach($classGroups->groupBy(fn($g) => $g->program->name) as $progName => $groups)
                            <optgroup label="{{ $progName }}">
                                @foreach($groups->sortBy(['year_level','name']) as $group)
                                <option value="{{ $group->id }}" {{ old('class_group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }} — Year {{ $group->year_level }}
                                    ({{ $group->students_count }} students)
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                @endif

                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-check-lg"></i> Create Section
                    </button>
                    <a href="{{ route('admin.sections.index') }}" class="btn-rupp-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection