
@extends('layouts.admin')
@section('title', 'Import Students')
@section('page-title', 'Import Students')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Import Students</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.users.index', ['role' => 'student']) }}">Students</a> /
            Import
        </div>
    </div>
    <a href="{{ route('admin.users.index', ['role' => 'student']) }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> Back to Students
    </a>
</div>

{{-- Import results --}}
@if(session('import_results'))
@php $results = session('import_results'); @endphp
<div style="margin-bottom:20px;">
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:12px; margin-bottom:16px;">
        <div style="background:#dcfce7; border:1px solid #86efac; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:28px; font-weight:700; color:#166534;">{{ $results['created'] }}</div>
            <div style="font-size:12px; color:#16a34a; margin-top:2px;">Students Created</div>
        </div>
        <div style="background:#dbeafe; border:1px solid #93c5fd; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:28px; font-weight:700; color:#1e40af;">{{ $results['enrolled'] }}</div>
            <div style="font-size:12px; color:#2563eb; margin-top:2px;">Enrolled in Sections</div>
        </div>
        <div style="background:#f3f4f6; border:1px solid #e5e7eb; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:28px; font-weight:700; color:#6b7280;">{{ $results['skipped'] }}</div>
            <div style="font-size:12px; color:#9ca3af; margin-top:2px;">Skipped</div>
        </div>
    </div>

    @if(!empty($results['errors']))
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5><i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b;"></i> Warnings / Errors</h5>
        </div>
        <div class="card-rupp-body" style="padding:0; max-height:250px; overflow-y:auto;">
            @foreach($results['errors'] as $error)
            <div style="padding:10px 16px; border-bottom:1px solid #f3f4f6; font-size:12.5px; color:#6b7280; display:flex; gap:8px; align-items:flex-start;">
                <i class="bi bi-info-circle" style="color:#f59e0b; flex-shrink:0; margin-top:1px;"></i>
                {{ $error }}
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- Upload form --}}
    <div>
        <div class="card-rupp">
            <div class="rupp-header-strip">
                <i class="bi bi-upload"></i>
                <h5>Upload Excel / CSV File</h5>
            </div>
            <div class="card-rupp-body">
                <form method="POST" action="{{ route('admin.students.import.store') }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- File upload area --}}
                    <div style="border:2px dashed #d1d5db; border-radius:10px; padding:28px; text-align:center; margin-bottom:16px; transition:border-color .15s; cursor:pointer;"
                        onclick="document.getElementById('fileInput').click()"
                        id="dropZone">
                        <i class="bi bi-file-earmark-excel-fill" style="font-size:36px; color:#16a34a; display:block; margin-bottom:10px;"></i>
                        <div style="font-size:14px; font-weight:500; color:#374151; margin-bottom:4px;">
                            Click to choose file
                        </div>
                        <div style="font-size:12px; color:#9ca3af;" id="fileName">
                            Accepts .xlsx, .xls, .csv — max 10MB
                        </div>
                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv"
                            style="display:none;" required
                            onchange="document.getElementById('fileName').textContent = this.files[0]?.name || 'No file chosen'">
                    </div>
                    @error('file')<div class="form-error" style="margin-bottom:12px;">{{ $message }}</div>@enderror

                    <button type="submit" class="btn-rupp-primary" style="width:100%; justify-content:center;">
                        <i class="bi bi-upload"></i> Import Students
                    </button>
                </form>
            </div>
        </div>

        {{-- Download template --}}
        <div class="card-rupp" style="margin-top:16px;">
            <div class="card-rupp-header">
                <h5><i class="bi bi-download" style="color:var(--rupp-gold)"></i> Download Template</h5>
            </div>
            <div class="card-rupp-body">
                <p style="font-size:13px; color:#6b7280; margin-bottom:14px; line-height:1.6;">
                    Download the CSV template, fill in student data, then upload it above.
                    The template includes example rows to guide you.
                </p>
                <a href="{{ route('admin.students.import.template') }}" class="btn-rupp-gold" style="width:100%; justify-content:center;">
                    <i class="bi bi-file-earmark-excel-fill"></i> Download Template (.csv)
                </a>
            </div>
        </div>
    </div>

    {{-- Instructions --}}
    <div>
        <div class="card-rupp">
            <div class="rupp-header-strip">
                <i class="bi bi-info-circle"></i>
                <h5>Column Guide</h5>
            </div>
            <div style="overflow-x:auto;">
                <table class="table-rupp">
                    <thead>
                        <tr>
                            <th>Column</th>
                            <th>Required</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">name</td>
                            <td><span class="badge-rupp badge-red" style="font-size:10px;">Required</span></td>
                            <td style="font-size:12px; color:#6b7280;">YOUNG Soklong</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">name_kh</td>
                            <td><span class="badge-rupp badge-gray" style="font-size:10px;">Optional</span></td>
                            <td style="font-size:12px; color:#6b7280; font-family:'Hanuman',serif;">យ៉ុង សុខឡុង</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">email</td>
                            <td><span class="badge-rupp badge-red" style="font-size:10px;">Required</span></td>
                            <td style="font-size:12px; color:#6b7280;">young@student.edu.kh</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">password</td>
                            <td><span class="badge-rupp badge-gray" style="font-size:10px;">Optional</span></td>
                            <td style="font-size:12px; color:#6b7280;">student1234 (default if empty)</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">student_id</td>
                            <td><span class="badge-rupp badge-red" style="font-size:10px;">Required</span></td>
                            <td style="font-size:12px; color:#6b7280;">2025-CS-001</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">program_code</td>
                            <td><span class="badge-rupp badge-red" style="font-size:10px;">Required</span></td>
                            <td style="font-size:12px; color:#6b7280;">BCS</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">year_level</td>
                            <td><span class="badge-rupp badge-red" style="font-size:10px;">Required</span></td>
                            <td style="font-size:12px; color:#6b7280;">3</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">date_of_birth</td>
                            <td><span class="badge-rupp badge-gray" style="font-size:10px;">Optional</span></td>
                            <td style="font-size:12px; color:#6b7280;">2002-03-15</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">course_code</td>
                            <td><span class="badge-rupp badge-gray" style="font-size:10px;">Optional</span></td>
                            <td style="font-size:12px; color:#6b7280;">CS301</td>
                        </tr>
                        <tr>
                            <td style="font-family:monospace; font-weight:600;">section_name</td>
                            <td><span class="badge-rupp badge-gray" style="font-size:10px;">Optional</span></td>
                            <td style="font-size:12px; color:#6b7280;">3CS-A</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Available programs --}}
        <div class="card-rupp" style="margin-top:16px;">
            <div class="card-rupp-header">
                <h5><i class="bi bi-mortarboard-fill" style="color:var(--rupp-gold)"></i> Available Program Codes</h5>
            </div>
            <div class="card-rupp-body" style="padding:14px 16px;">
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    @foreach($programs as $program)
                    <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:6px 12px; font-size:12px;">
                        <span style="font-family:monospace; font-weight:700; color:var(--rupp-green);">{{ $program->code }}</span>
                        <span style="color:#9ca3af; margin-left:6px;">{{ $program->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Available sections --}}
        <div class="card-rupp" style="margin-top:16px;">
            <div class="card-rupp-header">
                <h5><i class="bi bi-collection-fill" style="color:var(--rupp-gold)"></i> Available Sections</h5>
            </div>
            <div style="max-height:200px; overflow-y:auto;">
                <table class="table-rupp">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Section Name</th>
                            <th>Teacher</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sections as $section)
                        <tr>
                            <td style="font-family:monospace; font-weight:600; color:var(--rupp-green);">
                                {{ $section->course->code }}
                            </td>
                            <td>{{ $section->name }}</td>
                            <td style="font-size:12px; color:#6b7280;">
                                {{ $section->teacher?->user?->name ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Highlight drop zone on hover
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover', e => {
    e.preventDefault();
    dropZone.style.borderColor = 'var(--rupp-green)';
    dropZone.style.background  = '#f0fdf4';
});
dropZone.addEventListener('dragleave', () => {
    dropZone.style.borderColor = '#d1d5db';
    dropZone.style.background  = '';
});
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.style.borderColor = '#d1d5db';
    dropZone.style.background  = '';
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('fileInput').files = e.dataTransfer.files;
        document.getElementById('fileName').textContent = file.name;
    }
});
</script>
@endpush