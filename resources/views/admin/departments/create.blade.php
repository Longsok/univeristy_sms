@extends('layouts.admin')
@section('title', 'Add Department')
@section('page-title', 'Add Department')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Add New Department</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.departments.index') }}">Departments</a> / Add
        </div>
    </div>
</div>

<div style="max-width:560px;">
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-diagram-3"></i>
            <h5>Department Details</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.departments.store') }}">
                @csrf

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Faculty <span style="color:#ef4444">*</span></label>
                    <select name="faculty_id" class="form-select-rupp" required>
                        <option value="">— Select Faculty —</option>
                        @foreach($faculties as $faculty)
                            <option value="{{ $faculty->id }}"
                                {{ old('faculty_id') == $faculty->id ? 'selected' : '' }}>
                                {{ $faculty->name }} ({{ $faculty->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('faculty_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Department Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control-rupp"
                        placeholder="e.g. Department of Computer Science"
                        required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp">Code <span style="color:#ef4444">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="form-control-rupp"
                            placeholder="e.g. CS"
                            required>
                        @error('code')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Head Name</label>
                        <input type="text" name="head_name" value="{{ old('head_name') }}"
                            class="form-control-rupp"
                            placeholder="Dr. Name">
                    </div>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-check-lg"></i> Create Department
                    </button>
                    <a href="{{ route('admin.departments.index') }}" class="btn-rupp-outline">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection