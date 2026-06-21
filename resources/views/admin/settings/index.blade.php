@extends('layouts.admin')
@section('title', 'System Settings')
@section('page-title', 'System Settings')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>System Settings</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Settings
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert-success-rupp" style="margin-bottom:20px;">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}">
@csrf @method('PUT')

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- University Info --}}
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-building"></i>
            <h5>University Information</h5>
        </div>
        <div class="card-rupp-body">
            <div style="margin-bottom:14px;">
                <label class="form-label-rupp">University Name (English) <span style="color:#ef4444">*</span></label>
                <input type="text" name="university_name"
                    value="{{ $settings->get('university_name')?->value ?? 'Royal University of Phnom Penh' }}"
                    class="form-control-rupp" required>
            </div>
            <div style="margin-bottom:14px;">
                <label class="form-label-rupp" style="font-family:'Hanuman',serif;">
                    ឈ្មោះសាកលវិទ្យាល័យ (Khmer)
                </label>
                <input type="text" name="university_name_kh"
                    value="{{ $settings->get('university_name_kh')?->value ?? '' }}"
                    class="form-control-rupp"
                    style="font-family:'Hanuman',serif;"
                    placeholder="ឈ្មោះជាភាសាខ្មែរ">
            </div>
            <div style="margin-bottom:14px;">
                <label class="form-label-rupp">Short Name / Abbreviation <span style="color:#ef4444">*</span></label>
                <input type="text" name="university_short"
                    value="{{ $settings->get('university_short')?->value ?? 'RUPP' }}"
                    class="form-control-rupp" required>
            </div>
            <div style="margin-bottom:14px;">
                <label class="form-label-rupp">Current Academic Year <span style="color:#ef4444">*</span></label>
                <input type="text" name="academic_year"
                    value="{{ $settings->get('academic_year')?->value ?? '2025-2026' }}"
                    class="form-control-rupp"
                    placeholder="e.g. 2025-2026" required>
            </div>
            <div style="margin-bottom:14px;">
                <label class="form-label-rupp">Contact Email</label>
                <input type="email" name="contact_email"
                    value="{{ $settings->get('contact_email')?->value ?? '' }}"
                    class="form-control-rupp"
                    placeholder="admin@university.edu">
            </div>
            <div style="margin-bottom:14px;">
                <label class="form-label-rupp">Contact Phone</label>
                <input type="text" name="contact_phone"
                    value="{{ $settings->get('contact_phone')?->value ?? '' }}"
                    class="form-control-rupp"
                    placeholder="+855 xx xxx xxx">
            </div>
            <div>
                <label class="form-label-rupp">Address</label>
                <textarea name="address" rows="2" class="form-control-rupp">{{ $settings->get('address')?->value ?? '' }}</textarea>
            </div>
        </div>
    </div>

    {{-- Academic Settings --}}
    <div>
        <div class="card-rupp" style="margin-bottom:16px;">
            <div class="rupp-header-strip">
                <i class="bi bi-sliders"></i>
                <h5>Grading Settings</h5>
            </div>
            <div class="card-rupp-body">
                <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:10px 14px; margin-bottom:14px; font-size:12.5px; color:#166534;">
                    <strong>Current Grading Scale:</strong>
                    A(4.0) 85–100 · B+(3.5) 80–84 · B(3.0) 70–79 · C+(2.5) 65–69 · C(2.0) 50–64 · D(1.5) 45–49 · F(0.0) 0–44
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label class="form-label-rupp">Pass Threshold (%)</label>
                        <input type="number" name="pass_threshold"
                            value="{{ $settings->get('pass_threshold')?->value ?? '50' }}"
                            class="form-control-rupp" min="0" max="100" step="0.5">
                        <div style="font-size:11px; color:#9ca3af; margin-top:3px;">Minimum score to pass (currently {{ $settings->get('pass_threshold')?->value ?? '50' }})</div>
                    </div>
                    <div>
                        <label class="form-label-rupp">Re-exam Threshold (%)</label>
                        <input type="number" name="reexam_threshold"
                            value="{{ $settings->get('reexam_threshold')?->value ?? '45' }}"
                            class="form-control-rupp" min="0" max="100" step="0.5">
                        <div style="font-size:11px; color:#9ca3af; margin-top:3px;">Below pass but eligible for re-exam (currently {{ $settings->get('reexam_threshold')?->value ?? '45' }})</div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label class="form-label-rupp">Attendance Weeks</label>
                        <input type="number" name="attendance_weeks"
                            value="{{ $settings->get('attendance_weeks')?->value ?? '16' }}"
                            class="form-control-rupp" min="1" max="52">
                        <div style="font-size:11px; color:#9ca3af; margin-top:3px;">Total teaching weeks per semester</div>
                    </div>
                    <div>
                        <label class="form-label-rupp">Min GPA for Promotion</label>
                        <input type="number" name="min_gpa_promotion"
                            value="{{ $settings->get('min_gpa_promotion')?->value ?? '1.0' }}"
                            class="form-control-rupp" min="0" max="4" step="0.1">
                        <div style="font-size:11px; color:#9ca3af; margin-top:3px;">Minimum GPA required for year promotion</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save button --}}
        <button type="submit" class="btn-rupp-primary" style="width:100%; justify-content:center; padding:12px;">
            <i class="bi bi-floppy-fill"></i> Save All Settings
        </button>
    </div>

</div>
</form>
@endsection