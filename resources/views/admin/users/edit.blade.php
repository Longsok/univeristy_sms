@extends('layouts.admin')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit User</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.users.index') }}">Users</a> / Edit
        </div>
    </div>
</div>
 
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card-rupp">
        <div class="rupp-header-strip"><i class="bi bi-person-fill"></i><h5>Account Information</h5></div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PUT')

                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Full Name (English) <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="form-control-rupp" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">ឈ្មោះភាសាខ្មែរ (Khmer Name)</label>
                    <input type="text" name="name_kh" value="{{ old('name_kh', $user->name_kh) }}"
                        class="form-control-rupp"
                        placeholder="ឈ្មោះជាភាសាខ្មែរ"
                        style="font-family:'Hanuman',serif;">
                    @error('name_kh')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Email</label>
                    <input type="email" value="{{ $user->email }}" class="form-control-rupp"
                        disabled style="background:#f9fafb;color:#9ca3af;">
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="form-control-rupp">
                </div>

                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Address</label>
                    <textarea name="address" rows="2" class="form-control-rupp">{{ old('address', $user->address) }}</textarea>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="is_active" value="1"
                            {{ $user->is_active ? 'checked' : '' }}
                            style="accent-color:var(--rupp-green);">
                        Account Active
                    </label>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-floppy-fill"></i> Update
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn-rupp-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
 
    {{-- Role info (read only) --}}
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-{{ $user->isStudent() ? 'mortarboard' : 'person-workspace' }}-fill"></i>
            <h5>{{ ucfirst($user->role) }} Profile</h5>
        </div>
        <div class="card-rupp-body">
            @if($user->isStudent() && $user->student)
            <div style="display:grid;gap:12px;">
                <div>
                    <div class="form-label-rupp">Student ID</div>
                    <div style="font-size:14px;font-family:monospace;font-weight:600;color:var(--rupp-green);">
                        {{ $user->student->student_id }}
                    </div>
                </div>
                <div>
                    <div class="form-label-rupp">Program</div>
                    <div style="font-size:13px;color:#374151;">{{ $user->student->program->name }}</div>
                    <div style="font-size:11px;color:#9ca3af;">{{ $user->student->program->department->name }}</div>
                </div>
                <div>
                    <div class="form-label-rupp">Year Level</div>
                    <div style="font-size:13px;color:#374151;">Year {{ $user->student->year_level }}</div>
                </div>
                <div>
                    <div class="form-label-rupp">Date of Birth</div>
                    @if($user->student->date_of_birth)
                        <div style="font-size:13px; color:#374151;">
                            {{ \Carbon\Carbon::parse($user->student->date_of_birth)->format('d M Y') }}
                        </div>
                        <div style="font-size:11px; color:#9ca3af; margin-top:2px;">
                            Age: {{ \Carbon\Carbon::parse($user->student->date_of_birth)->age }} years old
                        </div>
                    @else
                        <span style="font-size:13px; color:#9ca3af;">Not provided</span>
                    @endif
                </div>
                <div>
                    <div class="form-label-rupp">Batch</div>
                    <form action="{{ route('admin.students.update-batch', $user->student) }}" method="POST"
                          style="display:flex; gap:8px; align-items:center; margin-top:4px;">
                        @csrf @method('PUT')
                        <input type="number" name="batch" value="{{ $user->student->batch }}"
                            min="1" max="999"
                            class="form-control-rupp"
                            style="width:100px; font-size:12px; padding:5px 10px;"
                            placeholder="e.g. 1">
                        <button type="submit" class="btn-rupp-outline" style="padding:5px 12px; font-size:12px; white-space:nowrap;">
                            <i class="bi bi-floppy-fill"></i> Save
                        </button>
                    </form>
                </div>
                <div>
                    <div class="form-label-rupp">Class Group</div>
                    @if($user->student->classGroup)
                        <span class="badge-rupp badge-green">{{ $user->student->classGroup->name }}</span>
                    @else
                        <span style="font-size:13px;color:#9ca3af;">Not assigned</span>
                    @endif
                </div>
                <div>
                    <div class="form-label-rupp">Status</div>
                    <span class="badge-rupp badge-green">{{ ucfirst($user->student->status) }}</span>
                </div>
                <div>
                    <div class="form-label-rupp">Scholarship Type</div>
                    <form action="{{ route('admin.students.scholarship', $user->student) }}" method="POST"
                          style="display:flex; gap:8px; align-items:center; margin-top:4px;">
                        @csrf @method('PUT')
                        <select name="scholarship_type" class="form-select-rupp"
                            style="font-size:12px; padding:5px 10px;" onchange="this.form.submit()">
                            <option value="paid"    {{ ($user->student->scholarship_type ?? 'paid') === 'paid'    ? 'selected' : '' }}>
                                💰 Self-Funded (Paid)
                            </option>
                            <option value="partial" {{ ($user->student->scholarship_type ?? 'paid') === 'partial' ? 'selected' : '' }}>
                                🎓 Partial Scholarship
                            </option>
                            <option value="full"    {{ ($user->student->scholarship_type ?? 'paid') === 'full'    ? 'selected' : '' }}>
                                ⭐ Full Scholarship
                            </option>
                        </select>
                    </form>
                </div>
            </div>

            @elseif($user->isTeacher() && $user->teacher)
            <div style="display:grid;gap:12px;">
                <div>
                    <div class="form-label-rupp">Employee ID</div>
                    <div style="font-size:14px;font-family:monospace;font-weight:600;color:var(--rupp-green);">
                        {{ $user->teacher->employee_id }}
                    </div>
                </div>
                <div>
                    <div class="form-label-rupp">Department</div>
                    <div style="font-size:13px;color:#374151;">{{ $user->teacher->department->name }}</div>
                    <div style="font-size:11px;color:#9ca3af;">{{ $user->teacher->department->faculty->name }}</div>
                </div>
                <div>
                    <div class="form-label-rupp">Specialization</div>
                    <div style="font-size:13px;color:#374151;">{{ $user->teacher->specialization ?? '—' }}</div>
                </div>
            </div>

            @else
            <div style="color:#9ca3af;font-size:13px;text-align:center;padding:20px;">
                Administrator account — no profile record.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection