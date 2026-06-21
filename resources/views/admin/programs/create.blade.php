@extends('layouts.admin')
@section('title', 'Add Program')
@section('page-title', 'Add Program')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Add New Program</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.programs.index') }}">Programs</a> / Add
        </div>
    </div>
</div>

<div style="max-width:600px;">
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-mortarboard"></i>
            <h5>Program Details</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.programs.store') }}">
                @csrf

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Department <span style="color:#ef4444">*</span></label>
                    <select name="department_id" class="form-select-rupp" required>
                        <option value="">— Select Department —</option>
                        @foreach($departments->groupBy(fn($d) => $d->faculty->name) as $facultyName => $depts)
                            <optgroup label="{{ $facultyName }}">
                                @foreach($depts as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }} ({{ $dept->code }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('department_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Program Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control-rupp"
                        placeholder="e.g. Bachelor of Computer Science"
                        required>
                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp">Code <span style="color:#ef4444">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="form-control-rupp"
                            placeholder="e.g. BCS"
                            required>
                        @error('code')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Degree Level <span style="color:#ef4444">*</span></label>
                        <select name="degree_level" class="form-select-rupp" required>
                            <option value="certificate" {{ old('degree_level') === 'certificate' ? 'selected' : '' }}>Certificate</option>
                            <option value="associate"   {{ old('degree_level') === 'associate'   ? 'selected' : '' }}>Associate</option>
                            <option value="bachelor"    {{ old('degree_level', 'bachelor') === 'bachelor' ? 'selected' : '' }}>Bachelor</option>
                            <option value="master"      {{ old('degree_level') === 'master'      ? 'selected' : '' }}>Master</option>
                            <option value="doctorate"   {{ old('degree_level') === 'doctorate'   ? 'selected' : '' }}>Doctorate</option>
                        </select>
                        @error('degree_level')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="form-label-rupp">Total Credits <span style="color:#ef4444">*</span></label>
                    <input type="number" name="total_credits"
                        value="{{ old('total_credits', 120) }}"
                        class="form-control-rupp"
                        min="30" max="300"
                        required>
                    @error('total_credits')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-check-lg"></i> Create Program
                    </button>
                    <a href="{{ route('admin.programs.index') }}" class="btn-rupp-outline">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection