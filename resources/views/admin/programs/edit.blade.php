@extends('layouts.admin')
@section('title', 'Edit Program')
@section('page-title', 'Edit Program')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Program</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.programs.index') }}">Programs</a> / Edit
        </div>
    </div>
</div>
<div style="max-width:600px;">
    <div class="card-rupp">
        <div class="rupp-header-strip"><i class="bi bi-mortarboard"></i><h5>Program Details</h5></div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.programs.update', $program) }}">
                @csrf @method('PUT')
                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Department <span style="color:#ef4444">*</span></label>
                    <select name="department_id" class="form-select-rupp" required>
                        @foreach(\App\Models\Department::with('faculty')->get() as $dept)
                            <option value="{{ $dept->id }}" {{ $program->department_id == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->faculty->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Program Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $program->name) }}" class="form-control-rupp" required>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label-rupp">Code <span style="color:#ef4444">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $program->code) }}" class="form-control-rupp" required>
                    </div>
                    <div>
                        <label class="form-label-rupp">Degree Level</label>
                        <select name="degree_level" class="form-select-rupp">
                            @foreach(['certificate','associate','bachelor','master','doctorate'] as $level)
                                <option value="{{ $level }}" {{ $program->degree_level === $level ? 'selected' : '' }}>{{ ucfirst($level) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Total Credits</label>
                    <input type="number" name="total_credits" value="{{ old('total_credits', $program->total_credits) }}" class="form-control-rupp">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="is_active" value="1" {{ $program->is_active ? 'checked' : '' }} style="accent-color:var(--rupp-green);">
                        Active
                    </label>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-rupp-primary"><i class="bi bi-floppy-fill"></i> Update</button>
                    <a href="{{ route('admin.programs.index') }}" class="btn-rupp-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection