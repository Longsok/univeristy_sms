@extends('layouts.admin')
@section('title', 'Add User')
@section('page-title', 'Add New User')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Add New User</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.users.index') }}">Users</a> / Add
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
@csrf
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

    {{-- Account information --}}
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-person-fill"></i>
            <h5>Account Information</h5>
        </div>
        <div class="card-rupp-body">

            {{-- Full Name English --}}
            <div style="margin-bottom:16px;">
                <label class="form-label-rupp">
                    Full Name (English) <span style="color:#ef4444">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="form-control-rupp {{ $errors->has('name') ? 'border-red-400' : '' }}"
                    placeholder="Full name in English">
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Khmer Name --}}
            <div style="margin-bottom:16px;">
                <label class="form-label-rupp" style="font-family:'Hanuman',serif;">
                    ឈ្មោះជាភាសាខ្មែរ
                    <span style="font-family:'Inter',sans-serif; font-size:11px; color:#9ca3af;">
                        (Khmer Name — optional)
                    </span>
                </label>
                <input type="text" name="name_kh" value="{{ old('name_kh') }}"
                    class="form-control-rupp"
                    placeholder="ឈ្មោះខ្មែរ"
                    style="font-family:'Hanuman',serif;">
                @error('name_kh')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div style="margin-bottom:16px;">
                <label class="form-label-rupp">Email Address <span style="color:#ef4444">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control-rupp"
                    placeholder="user@university.edu">
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div style="margin-bottom:16px;">
                <label class="form-label-rupp">Password <span style="color:#ef4444">*</span></label>
                <input type="password" name="password"
                    class="form-control-rupp" placeholder="Min. 8 characters">
                @error('password')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Confirm Password --}}
            <div style="margin-bottom:16px;">
                <label class="form-label-rupp">Confirm Password <span style="color:#ef4444">*</span></label>
                <input type="password" name="password_confirmation" class="form-control-rupp">
            </div>

            {{-- Role --}}
            <div style="margin-bottom:16px;">
                <label class="form-label-rupp">Role <span style="color:#ef4444">*</span></label>
                <select name="role" class="form-select-rupp" id="roleSelect" onchange="toggleRoleFields()">
                    <option value="">— Select Role —</option>
                    <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                    <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                </select>
                @error('role')<div class="form-error">{{ $message }}</div>@enderror
            </div>

            {{-- Phone --}}
            <div style="margin-bottom:16px;">
                <label class="form-label-rupp">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="form-control-rupp" placeholder="+855 xx xxx xxx">
            </div>

        </div>
    </div>

    {{-- Role-specific fields --}}
    <div>

        {{-- Student fields --}}
        <div class="card-rupp" id="studentFields" style="display:none; margin-bottom:20px;">
            <div class="rupp-header-strip">
                <i class="bi bi-mortarboard-fill"></i>
                <h5>Student Details</h5>
            </div>
            <div class="card-rupp-body">

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Student ID <span style="color:#ef4444">*</span></label>
                    <input type="text" name="student_id" value="{{ old('student_id') }}"
                        class="form-control-rupp" placeholder="e.g. 2024-CS-001">
                    @error('student_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Program <span style="color:#ef4444">*</span></label>
                    <select name="program_id" class="form-select-rupp">
                        <option value="">— Select Program —</option>
                        @foreach($programs->groupBy(fn($p) => $p->department->faculty->name) as $facultyName => $facultyPrograms)
                            <optgroup label="{{ $facultyName }}">
                                @foreach($facultyPrograms as $program)
                                    <option value="{{ $program->id }}"
                                        {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }} ({{ $program->department->name }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('program_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp">Year Level <span style="color:#ef4444">*</span></label>
                        <select name="year_level" class="form-select-rupp">
                            @for($i = 1; $i <= 6; $i++)
                                <option value="{{ $i }}"
                                    {{ old('year_level') == $i ? 'selected' : '' }}>
                                    Year {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="form-label-rupp">Date of Birth</label>
                        <input type="date" name="date_of_birth"
                            value="{{ old('date_of_birth') }}" class="form-control-rupp">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Batch</label>
                    <input type="number" name="batch"
                        value="{{ old('batch') }}"
                        class="form-control-rupp"
                        placeholder="e.g. 1, 2, 3"
                        min="1" max="999">
                    <div style="font-size:11px; color:#9ca3af; margin-top:3px;">
                        Which batch/intake does this student belong to? (Batch 1 = first intake)
                    </div>
                </div>

            </div>
        </div>

        {{-- Teacher fields --}}
        <div class="card-rupp" id="teacherFields" style="display:none; margin-bottom:20px;">
            <div class="rupp-header-strip">
                <i class="bi bi-person-workspace"></i>
                <h5>Teacher Details</h5>
            </div>
            <div class="card-rupp-body">

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Employee ID <span style="color:#ef4444">*</span></label>
                    <input type="text" name="employee_id" value="{{ old('employee_id') }}"
                        class="form-control-rupp" placeholder="e.g. T-0001">
                    @error('employee_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Department <span style="color:#ef4444">*</span></label>
                    <select name="department_id" class="form-select-rupp">
                        <option value="">— Select Department —</option>
                        @foreach($departments->groupBy(fn($d) => $d->faculty->name) as $facultyName => $depts)
                            <optgroup label="{{ $facultyName }}">
                                @foreach($depts as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('department_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label-rupp">Specialization</label>
                    <input type="text" name="specialization" value="{{ old('specialization') }}"
                        class="form-control-rupp" placeholder="e.g. Database Systems">
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn-rupp-primary">
                <i class="bi bi-person-plus-fill"></i> Create User
            </button>
            <a href="{{ route('admin.users.index') }}" class="btn-rupp-outline">
                Cancel
            </a>
        </div>

    </div>

</div>
</form>
@endsection

@push('scripts')
<script>
function toggleRoleFields() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('studentFields').style.display = role === 'student' ? 'block' : 'none';
    document.getElementById('teacherFields').style.display = role === 'teacher' ? 'block' : 'none';
}
// Run on page load in case of old() values
toggleRoleFields();
</script>
@endpush