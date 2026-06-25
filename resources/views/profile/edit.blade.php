@extends('layouts.' . Auth::user()->role)
@section('title', 'Edit Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>My Profile</h1>
        <div class="breadcrumb-text">Update your personal information</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 280px 1fr; gap:20px;">

    {{-- Profile photo card --}}
    <div>
        <div class="card-rupp" style="margin-bottom:16px;">
            <div class="rupp-header-strip">
                <i class="bi bi-person-circle"></i>
                <h5>Profile Photo</h5>
            </div>
            <div class="card-rupp-body" style="text-align:center;">
                <div style="width:90px;height:90px;border-radius:50%;overflow:hidden;margin:0 auto 16px;background:var(--rupp-gold-pale);display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#92680a;border:3px solid var(--rupp-gold);">
                    @if(Auth::user()->photo)
                        <img src="{{ Auth::user()->photo_url }}"
                             style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.style.display='none'; document.getElementById('avatar-initial').style.display='flex';">
                        <span id="avatar-initial" style="display:none; width:100%; height:100%; align-items:center; justify-content:center; font-size:32px; font-weight:700; color:#92680a;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </div>

                {{-- English name --}}
                <div style="font-size:14px; font-weight:600; color:#111827;">
                    {{ Auth::user()->name }}
                </div>

                {{-- Khmer name (shown if exists) --}}
                @if(Auth::user()->name_kh)
                <div style="font-size:13px; color:#6b7280; font-family:'Hanuman',serif; margin-top:2px;">
                    {{ Auth::user()->name_kh }}
                </div>
                @endif

                <div style="font-size:12px; color:#9ca3af; margin-top:4px;">
                    {{ ucfirst(Auth::user()->role) }}
                </div>

                @if(Auth::user()->isStudent())
                    <div style="margin-top:8px;">
                        <span class="badge-rupp badge-green">{{ Auth::user()->student?->student_id }}</span>
                    </div>
                    <div style="font-size:12px; color:#6b7280; margin-top:6px;">
                        {{ Auth::user()->student?->program?->name }}
                    </div>
                @elseif(Auth::user()->isTeacher())
                    <div style="margin-top:8px;">
                        <span class="badge-rupp badge-gold">{{ Auth::user()->teacher?->employee_id }}</span>
                    </div>
                    <div style="font-size:12px; color:#6b7280; margin-top:6px;">
                        {{ Auth::user()->teacher?->department?->name }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Change password --}}
        <div class="card-rupp">
            <div class="rupp-header-strip">
                <i class="bi bi-lock-fill"></i>
                <h5>Change Password</h5>
            </div>
            <div class="card-rupp-body">
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PUT')
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Current Password</label>
                        <input type="password" name="current_password" class="form-control-rupp" placeholder="••••••••">
                        @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">New Password</label>
                        <input type="password" name="password" class="form-control-rupp" placeholder="Min. 8 characters">
                        @error('password')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div style="margin-bottom:16px;">
                        <label class="form-label-rupp">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control-rupp" placeholder="Repeat new password">
                    </div>
                    <button type="submit" class="btn-rupp-primary" style="width:100%;">
                        <i class="bi bi-lock-fill"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Main profile form --}}
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-pencil-fill"></i>
            <h5>Personal Information</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                {{-- English name + Email --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp">Full Name (English) <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name"
                            value="{{ old('name', Auth::user()->name) }}"
                            class="form-control-rupp"
                            placeholder="Full name in English">
                        @error('name')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Email Address</label>
                        <input type="email" value="{{ Auth::user()->email }}"
                            class="form-control-rupp" disabled
                            style="background:#f9fafb; color:#9ca3af;">
                        <div style="font-size:11px; color:#9ca3af; margin-top:4px;">
                            Email cannot be changed. Contact admin.
                        </div>
                    </div>
                </div>

                {{-- Khmer name --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp" style="font-family:'Hanuman',serif;">
                            ឈ្មោះជាភាសាខ្មែរ
                            <span style="font-family:'Inter',sans-serif; font-size:11px; color:#9ca3af;">(Khmer Name — optional)</span>
                        </label>
                        <input type="text" name="name_kh"
                            value="{{ old('name_kh', Auth::user()->name_kh) }}"
                            class="form-control-rupp"
                            placeholder="ឈ្មោះខ្មែរ"
                            style="font-family:'Hanuman',serif;">
                        @error('name_kh')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        {{-- spacer --}}
                    </div>
                </div>

                {{-- Phone + Photo --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="form-label-rupp">Phone Number</label>
                        <input type="text" name="phone"
                            value="{{ old('phone', Auth::user()->phone) }}"
                            class="form-control-rupp"
                            placeholder="+855 xx xxx xxx">
                        @error('phone')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">Profile Photo</label>
                        <input type="file" name="photo" accept="image/*"
                            class="form-control-rupp" style="padding:7px 12px;">
                        @error('photo')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Address --}}
                <div style="margin-bottom:20px;">
                    <label class="form-label-rupp">Address</label>
                    <textarea name="address" rows="3" class="form-control-rupp"
                        placeholder="Your current address">{{ old('address', Auth::user()->address) }}</textarea>
                    @error('address')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                {{-- Read-only academic info --}}
                @if(Auth::user()->isStudent())
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin-bottom:20px;">
                    <div style="font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:12px;">
                        Academic Information
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                        <div>
                            <div style="font-size:11px; color:#9ca3af;">Student ID</div>
                            <div style="font-size:13px; font-weight:600; color:#111827; font-family:monospace;">
                                {{ Auth::user()->student?->student_id }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:#9ca3af;">Year Level</div>
                            <div style="font-size:13px; font-weight:600; color:#111827;">
                                Year {{ Auth::user()->student?->year_level }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:#9ca3af;">Program</div>
                            <div style="font-size:13px; font-weight:600; color:#111827;">
                                {{ Auth::user()->student?->program?->code }}
                            </div>
                        </div>
                    </div>
                </div>
                @elseif(Auth::user()->isTeacher())
                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:16px; margin-bottom:20px;">
                    <div style="font-size:12px; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em; margin-bottom:12px;">
                        Employment Information
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                        <div>
                            <div style="font-size:11px; color:#9ca3af;">Employee ID</div>
                            <div style="font-size:13px; font-weight:600; color:#111827; font-family:monospace;">
                                {{ Auth::user()->teacher?->employee_id }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:#9ca3af;">Department</div>
                            <div style="font-size:13px; font-weight:600; color:#111827;">
                                {{ Auth::user()->teacher?->department?->name }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size:11px; color:#9ca3af;">Specialization</div>
                            <div style="font-size:13px; font-weight:600; color:#111827;">
                                {{ Auth::user()->teacher?->specialization ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn-rupp-primary">
                        <i class="bi bi-floppy-fill"></i> Save Changes
                    </button>
                    <a href="{{ url()->previous() }}" class="btn-rupp-outline">Cancel</a>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection