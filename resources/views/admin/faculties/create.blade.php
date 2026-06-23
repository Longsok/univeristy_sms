@extends('layouts.admin')
@section('title', 'Add Faculty')
@section('page-title', 'Add Faculty')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Add Faculty</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.faculties.index') }}">Faculties</a> / Add
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
            <form method="POST" action="{{ route('admin.faculties.store') }}">
                @csrf
                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Faculty Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control-rupp" placeholder="e.g. Faculty of Engineering" required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="form-label-rupp">Code <span style="color:#ef4444">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="form-control-rupp" placeholder="e.g. FE" required>
                        @error('code')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Dean Name</label>
                        <input type="text" name="dean_name" value="{{ old('dean_name') }}"
                            class="form-control-rupp" placeholder="e.g. Prof. SOK Dara">
                    </div>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-plus-lg"></i> Create Faculty
                    </button>
                    <a href="{{ route('admin.faculties.index') }}" class="btn-rupp-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection