@extends('layouts.admin')
@section('title', 'Edit Faculty')
@section('page-title', 'Edit Faculty')
 
@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Faculty</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.faculties.index') }}">Faculties</a> / Edit
        </div>
    </div>
</div>
 
<div style="max-width:600px;">
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-building"></i>
            <h5>Faculty Details</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.faculties.update', $faculty) }}">
                @csrf @method('PUT')
                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Faculty Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $faculty->name) }}" class="form-control-rupp" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label-rupp">Code <span style="color:#ef4444">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $faculty->code) }}" class="form-control-rupp" required>
                        @error('code')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Dean Name</label>
                        <input type="text" name="dean_name" value="{{ old('dean_name', $faculty->dean_name) }}" class="form-control-rupp">
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="is_active" value="1" {{ $faculty->is_active ? 'checked' : '' }} style="accent-color:var(--rupp-green);">
                        Active
                    </label>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-rupp-primary"><i class="bi bi-floppy-fill"></i> Update</button>
                    <a href="{{ route('admin.faculties.index') }}" class="btn-rupp-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection