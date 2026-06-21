@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ request('role') ? ucfirst(request('role')) . 's' : 'All Users' }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Users
        </div>
    </div>
    <div style="display:flex; gap:10px;">
        @if(!request('role') || request('role') === 'student')
        <a href="{{ route('admin.students.import') }}" class="btn-rupp-outline">
            <i class="bi bi-file-earmark-excel-fill"></i> Import Students
        </a>
        @endif
        <a href="{{ route('admin.users.create') }}" class="btn-rupp-primary">
            <i class="bi bi-person-plus-fill"></i> Add User
        </a>
    </div>
</div>

{{-- Filter bar --}}
<div class="card-rupp" style="margin-bottom: 20px;">
    <div class="card-rupp-body" style="padding: 14px 20px;">
        <form method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div style="position:relative; flex:1; min-width:200px;">
                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:14px;"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by name or email..."
                    style="width:100%; padding:8px 12px 8px 34px; border:1px solid #d1d5db; border-radius:8px; font-size:13.5px; outline:none; font-family:'Inter',sans-serif;">
            </div>
            <select name="role" style="padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:13.5px; outline:none; background:#fff;">
                <option value="">All Roles</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Students</option>
                <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teachers</option>
            </select>
            <button type="submit" class="btn-rupp-primary" style="padding:8px 16px;">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if(request('search') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="btn-rupp-outline" style="padding:7px 14px; font-size:13px;">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card-rupp">
    <div style="overflow-x: auto;">
        <table class="table-rupp">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Department / Program</th>
                    <th>ID Number</th>
                    <th>Batch / Class</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;background:{{ $user->isStudent() ? '#dcfce7' : '#fdf3d7' }};display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;color:{{ $user->isStudent() ? '#166534' : '#92680a' }};">
                                @if($user->photo)
                                    <img src="{{ $user->photo_url }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <div style="font-weight:500; font-size:13.5px;">{{ $user->name }}</div>
                                @if($user->name_kh)
                                <div style="font-size:11.5px; color:#9ca3af; font-family:'Hanuman',serif;">{{ $user->name_kh }}</div>
                                @endif
                                <div style="font-size:12px; color:#9ca3af;">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-rupp {{ $user->isTeacher() ? 'badge-gold' : 'badge-green' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td style="font-size:12.5px; color:#6b7280;">
                        @if($user->isStudent())
                            {{ $user->student?->program?->name ?? '—' }}
                        @elseif($user->isTeacher())
                            {{ $user->teacher?->department?->name ?? '—' }}
                        @endif
                    </td>
                    <td style="font-size:12.5px; color:#374151; font-family:monospace;">
                        {{ $user->isStudent() ? $user->student?->student_id : $user->teacher?->employee_id }}
                    </td>
                    <td>
                        <span class="badge-rupp {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                            <i class="bi bi-circle-fill" style="font-size:6px;"></i>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn-icon edit" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="btn-icon {{ $user->is_active ? 'delete' : 'view' }}"
                                    title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi bi-{{ $user->is_active ? 'person-slash' : 'person-check' }}-fill"></i>
                                </button>
                            </form>
                            {{-- Reset Password button → opens modal --}}
                            <button type="button"
                                class="btn-icon"
                                style="background:#f3e8ff; color:#7c3aed;"
                                title="Reset Password"
                                onclick="openResetModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->role }}')">
                                <i class="bi bi-key-fill"></i>
                            </button>
                            {{-- Delete user button → opens modal --}}
                            <button type="button"
                                class="btn-icon delete"
                                title="Delete User"
                                onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->role }}')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:40px; color:#9ca3af;">
                        <i class="bi bi-people" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                        No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
        <div style="font-size:13px; color:#6b7280;">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
        </div>
        {{ $users->onEachSide(1)->links() }}
    </div>
    @endif
</div>

{{-- ── Reset Password Modal ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:420px;">
        <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none;">

            <div style="background:#7c3aed; padding:16px 20px; display:flex; align-items:center; gap:10px;">
                <i class="bi bi-key-fill" style="color:#e9d5ff; font-size:18px;"></i>
                <h5 style="color:#fff; font-size:14px; font-weight:600; margin:0;">Reset Password</h5>
            </div>

            <div style="padding:24px;">

                {{-- User info strip --}}
                <div style="background:#f5f3ff; border:1px solid #e9d5ff; border-radius:8px; padding:10px 14px; margin-bottom:18px; font-size:13px; color:#6d28d9; display:flex; align-items:center; gap:8px;">
                    <i class="bi bi-person-fill"></i>
                    <span id="resetUserName" style="font-weight:600;"></span>
                    <span id="resetUserRole" style="font-size:11px; background:#ede9fe; padding:2px 8px; border-radius:10px;"></span>
                </div>

                <form id="resetPasswordForm" method="POST" action="">
                    @csrf

                    {{-- New password --}}
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">New Password <span style="color:#ef4444">*</span></label>
                        <div style="position:relative;">
                            <input type="password" name="password" id="newPassword"
                                class="form-control-rupp"
                                placeholder="Enter new password"
                                minlength="8" required
                                style="padding-right:40px;"
                                oninput="checkMatch()">
                            <button type="button" onclick="togglePwd('newPassword','eyeNew')"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;">
                                <i class="bi bi-eye-fill" id="eyeNew"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Confirm password --}}
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Confirm Password <span style="color:#ef4444">*</span></label>
                        <div style="position:relative;">
                            <input type="password" name="password_confirmation" id="confirmPassword"
                                class="form-control-rupp"
                                placeholder="Confirm new password"
                                minlength="8" required
                                style="padding-right:40px;"
                                oninput="checkMatch()">
                            <button type="button" onclick="togglePwd('confirmPassword','eyeConfirm')"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;">
                                <i class="bi bi-eye-fill" id="eyeConfirm"></i>
                            </button>
                        </div>
                        <div id="pwdMatchMsg" style="font-size:12px;margin-top:4px;min-height:18px;"></div>
                    </div>

                    {{-- Quick fill --}}
                    <div style="margin-bottom:18px;">
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:6px;">Quick fill defaults:</div>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <button type="button" onclick="quickFill('student1234')"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">
                                student1234
                            </button>
                            <button type="button" onclick="quickFill('teacher1234')"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">
                                teacher1234
                            </button>
                            <button type="button" onclick="quickFill('rupp1234')"
                                class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">
                                rupp1234
                            </button>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <button type="submit"
                            style="background:#7c3aed; color:#fff; border:none; padding:9px 18px; border-radius:8px; font-size:13.5px; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                            <i class="bi bi-key-fill"></i> Reset Password
                        </button>
                        <button type="button" class="btn-rupp-outline" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Delete User Modal ───────────────────────────────────────────────────── --}}
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:440px;">
        <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none;">
            <div style="background:#dc2626; padding:16px 20px; display:flex; align-items:center; gap:10px;">
                <i class="bi bi-trash-fill" style="color:#fca5a5; font-size:18px;"></i>
                <h5 style="color:#fff; font-size:14px; font-weight:600; margin:0;">Delete User</h5>
            </div>
            <div style="padding:24px;">
                <div id="deleteUserInfo" style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:12px 14px; margin-bottom:18px;">
                    <div style="font-size:13px; font-weight:600; color:#991b1b; margin-bottom:4px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        This action cannot be undone!
                    </div>
                    <div style="font-size:13px; color:#6b7280;">
                        You are about to permanently delete:
                        <strong id="deleteUserName" style="color:#111827;"></strong>
                        <span id="deleteUserRole" style="font-size:11px; background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:10px; margin-left:4px;"></span>
                    </div>
                </div>

                <div style="background:#fff7ed; border:1px solid #fdba74; border-radius:8px; padding:12px 14px; margin-bottom:18px; font-size:12.5px; color:#92400e;">
                    <strong>This will also delete:</strong>
                    <ul style="margin:6px 0 0; padding-left:18px; line-height:1.8;">
                        <li>All enrollment records</li>
                        <li>All grade records</li>
                        <li>All attendance records</li>
                        <li>Student/Teacher profile</li>
                    </ul>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Type <strong>DELETE</strong> to confirm <span style="color:#ef4444">*</span></label>
                    <input type="text" id="deleteConfirmInput"
                        class="form-control-rupp"
                        placeholder="Type DELETE to confirm"
                        oninput="checkDeleteConfirm()"
                        style="border-color:#fca5a5;">
                    <div id="deleteConfirmMsg" style="font-size:12px; margin-top:4px; min-height:16px;"></div>
                </div>

                <form id="deleteUserForm" method="POST" action="">
                    @csrf @method('DELETE')
                    <div style="display:flex; gap:10px;">
                        <button type="submit" id="deleteSubmitBtn"
                            disabled
                            style="background:#dc2626; color:#fff; border:none; padding:9px 18px; border-radius:8px; font-size:13.5px; font-weight:500; cursor:not-allowed; opacity:0.5; display:inline-flex; align-items:center; gap:6px;"
                            id="deleteSubmitBtn">
                            <i class="bi bi-trash-fill"></i> Delete Permanently
                        </button>
                        <button type="button" class="btn-rupp-outline" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openResetModal(userId, userName, userRole) {
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('resetUserRole').textContent = userRole.charAt(0).toUpperCase() + userRole.slice(1);
    document.getElementById('resetPasswordForm').action  = `/admin/users/${userId}/reset-password`;
    document.getElementById('newPassword').value         = '';
    document.getElementById('confirmPassword').value     = '';
    document.getElementById('pwdMatchMsg').textContent   = '';
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'bi bi-eye-slash-fill';
    } else {
        input.type     = 'password';
        icon.className = 'bi bi-eye-fill';
    }
}

function quickFill(password) {
    document.getElementById('newPassword').value     = password;
    document.getElementById('confirmPassword').value = password;
    checkMatch();
}

function checkMatch() {
    const pwd     = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    const msg     = document.getElementById('pwdMatchMsg');
    if (!confirm) { msg.textContent = ''; return; }
    if (pwd === confirm) {
        msg.innerHTML   = '<span style="color:#166534;">✓ Passwords match</span>';
    } else {
        msg.innerHTML   = '<span style="color:#991b1b;">✗ Passwords do not match</span>';
    }
}

document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
    const pwd     = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    if (pwd !== confirm) {
        e.preventDefault();
        document.getElementById('pwdMatchMsg').innerHTML = '<span style="color:#991b1b;">✗ Passwords do not match</span>';
    }
});

function openDeleteModal(userId, userName, userRole) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteUserRole').textContent = userRole.charAt(0).toUpperCase() + userRole.slice(1);
    document.getElementById('deleteUserForm').action      = `/admin/users/${userId}`;
    document.getElementById('deleteConfirmInput').value   = '';
    document.getElementById('deleteConfirmMsg').textContent = '';
    const btn = document.getElementById('deleteSubmitBtn');
    btn.disabled = true;
    btn.style.opacity = '0.5';
    btn.style.cursor  = 'not-allowed';
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}

function checkDeleteConfirm() {
    const val = document.getElementById('deleteConfirmInput').value;
    const msg = document.getElementById('deleteConfirmMsg');
    const btn = document.getElementById('deleteSubmitBtn');
    if (val === 'DELETE') {
        msg.innerHTML = '<span style="color:#166534;">✓ Confirmed</span>';
        btn.disabled      = false;
        btn.style.opacity = '1';
        btn.style.cursor  = 'pointer';
    } else {
        msg.innerHTML = val.length > 0 ? '<span style="color:#991b1b;">Type DELETE in uppercase</span>' : '';
        btn.disabled      = true;
        btn.style.opacity = '0.5';
        btn.style.cursor  = 'not-allowed';
    }
}
</script>
@endpush