@extends('layouts.admin')
@section('title', 'Edit Department')
@section('page-title', 'Edit Department')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Department</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.departments.index') }}">Departments</a> / Edit
        </div>
    </div>
</div>
<div style="max-width:600px;">
    <div class="card-rupp">
        <div class="rupp-header-strip"><i class="bi bi-diagram-3"></i><h5>Department Details</h5></div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                @csrf @method('PUT')
                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Faculty <span style="color:#ef4444">*</span></label>
                    <select name="faculty_id" class="form-select-rupp" required>
                        @foreach(\App\Models\Faculty::where('is_active',true)->get() as $faculty)
                            <option value="{{ $faculty->id }}" {{ $department->faculty_id == $faculty->id ? 'selected' : '' }}>{{ $faculty->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Department Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $department->name) }}" class="form-control-rupp" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label-rupp">Code <span style="color:#ef4444">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $department->code) }}" class="form-control-rupp" required>
                    </div>
                    <div>
                        <label class="form-label-rupp">Head Name</label>
                        <input type="text" name="head_name" value="{{ old('head_name', $department->head_name) }}" class="form-control-rupp">
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="is_active" value="1" {{ $department->is_active ? 'checked' : '' }} style="accent-color:var(--rupp-green);">
                        Active
                    </label>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-rupp-primary"><i class="bi bi-floppy-fill"></i> Update</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn-rupp-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
 