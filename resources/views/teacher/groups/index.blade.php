@extends('layouts.teacher')
@section('title', 'Student Groups')
@section('page-title', 'Student Groups')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $section->course->name }} — Groups</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('teacher.courses.index') }}">Courses</a> / Groups
        </div>
    </div>
    <button class="btn-rupp-primary" onclick="openCreateModal()">
        <i class="bi bi-plus-lg"></i> New Group
    </button>
</div>

{{-- Summary bar --}}
<div style="background:#f0fdf4; border:1px solid #86efac; border-radius:10px; padding:12px 18px; margin-bottom:20px; display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
    <span style="font-size:13px; color:#166534;">
        <i class="bi bi-people-fill"></i>
        <strong>{{ $groups->count() }}</strong> groups
    </span>
    <span style="font-size:13px; color:#166534;">
        <i class="bi bi-person-check-fill"></i>
        <strong>{{ $groups->flatMap(fn($g) => $g->members)->count() }}</strong> assigned
    </span>
    <span style="font-size:13px; color:{{ $unassigned->count() > 0 ? '#92400e' : '#166534' }};">
        <i class="bi bi-person-dash-fill"></i>
        <strong>{{ $unassigned->count() }}</strong> unassigned
    </span>
</div>

<div style="display:grid; grid-template-columns:1fr 280px; gap:20px; align-items:start;">

    {{-- Left: Groups --}}
    <div>
        @forelse($groups as $group)
        <div class="card-rupp" style="margin-bottom:16px;">

            {{-- Group header --}}
            <div style="padding:14px 18px; background:#f9fafb; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; background:var(--rupp-green); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="bi bi-people-fill" style="color:var(--rupp-gold); font-size:15px;"></i>
                    </div>
                    <div>
                        {{-- Editable group name --}}
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span id="name-display-{{ $group->id }}"
                                style="font-size:15px; font-weight:600; color:#111827;">
                                {{ $group->name }}
                            </span>
                            <button onclick="startEditName({{ $group->id }}, '{{ addslashes($group->name) }}')"
                                style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:0;"
                                title="Rename group">
                                <i class="bi bi-pencil-fill" style="font-size:12px;"></i>
                            </button>
                        </div>
                        {{-- Edit name inline form --}}
                        <form id="name-form-{{ $group->id }}"
                            action="{{ route('teacher.groups.rename', $group) }}"
                            method="POST"
                            style="display:none; margin-top:4px;">
                            @csrf @method('PUT')
                            <div style="display:flex; gap:6px; align-items:center;">
                                <input type="text" name="name" id="name-input-{{ $group->id }}"
                                    class="form-control-rupp" style="padding:4px 8px; font-size:13px; width:180px;">
                                <button type="submit" class="btn-rupp-primary" style="padding:4px 10px; font-size:12px;">
                                    Save
                                </button>
                                <button type="button" onclick="cancelEditName({{ $group->id }})"
                                    class="btn-rupp-outline" style="padding:4px 10px; font-size:12px;">
                                    Cancel
                                </button>
                            </div>
                        </form>

                        <div style="font-size:11.5px; color:#9ca3af; margin-top:2px;">
                            {{ $group->members->count() }} members
                            @if($group->members->where('role','leader')->count())
                            · <span style="color:var(--rupp-gold);">
                                <i class="bi bi-star-fill" style="font-size:10px;"></i>
                                Leader: {{ $group->members->where('role','leader')->first()?->student?->user?->name }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Group actions --}}
                <div style="display:flex; gap:6px;">
                    <form action="{{ route('teacher.groups.destroy', $group) }}" method="POST"
                        onsubmit="return confirm('Delete {{ addslashes($group->name) }}? All members will be unassigned.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon delete" title="Delete group">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div style="padding:16px 18px;">

                {{-- Current members --}}
                @if($group->members->count())
                <div style="margin-bottom:14px;">
                    <div style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">Members</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px;">
                        @foreach($group->members->sortByDesc(fn($m) => $m->role === 'leader') as $member)
                        <div style="display:flex; align-items:center; gap:6px; background:{{ $member->role === 'leader' ? '#fef3c7' : '#f9fafb' }}; border:1px solid {{ $member->role === 'leader' ? '#fde68a' : '#e5e7eb' }}; border-radius:20px; padding:5px 12px;">
                            @if($member->role === 'leader')
                            <i class="bi bi-star-fill" style="color:var(--rupp-gold); font-size:11px;"></i>
                            @endif
                            <span style="font-size:12.5px; font-weight:{{ $member->role === 'leader' ? '600' : '400' }};">
                                {{ $member->student->user->name }}
                            </span>
                            {{-- Set leader button --}}
                            @if($member->role !== 'leader')
                            <form action="{{ route('teacher.groups.leader', $group) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $member->student->id }}">
                                <button type="submit" title="Set as leader"
                                    style="background:none; border:none; cursor:pointer; color:#d1d5db; padding:0; line-height:1;"
                                    onmouseover="this.style.color='var(--rupp-gold)'"
                                    onmouseout="this.style.color='#d1d5db'">
                                    <i class="bi bi-star" style="font-size:11px;"></i>
                                </button>
                            </form>
                            @endif
                            {{-- Remove member --}}
                            <form action="{{ route('teacher.groups.remove-member', [$group, $member->student]) }}"
                                method="POST" style="display:inline;"
                                onsubmit="return confirm('Remove {{ addslashes($member->student->user->name) }} from group?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Remove"
                                    style="background:none; border:none; cursor:pointer; color:#d1d5db; padding:0; line-height:1;"
                                    onmouseover="this.style.color='#ef4444'"
                                    onmouseout="this.style.color='#d1d5db'">
                                    <i class="bi bi-x" style="font-size:15px;"></i>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div style="font-size:13px; color:#9ca3af; margin-bottom:14px; padding:12px; background:#f9fafb; border-radius:8px; text-align:center;">
                    <i class="bi bi-person-plus" style="font-size:18px; display:block; margin-bottom:4px;"></i>
                    No members yet — add students from the unassigned list
                </div>
                @endif

                {{-- Add members from unassigned --}}
                @if($unassigned->count() > 0)
                <form method="POST" action="{{ route('teacher.groups.assign', $group) }}">
                    @csrf
                    <div style="font-size:11px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">Add Members</div>
                    <div style="display:flex; gap:8px; align-items:flex-start;">
                        <select name="student_ids[]" multiple class="form-select-rupp"
                            style="flex:1; height:90px; font-size:12px;">
                            @foreach($unassigned as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->user->name }} — {{ $student->student_id }}
                            </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-rupp-primary" style="padding:8px 14px; font-size:12px; white-space:nowrap;">
                            <i class="bi bi-person-plus-fill"></i> Add
                        </button>
                    </div>
                    <div style="font-size:11px; color:#9ca3af; margin-top:4px;">Hold Ctrl/Cmd to select multiple students</div>
                </form>
                @else
                <div style="font-size:12px; color:#16a34a; background:#f0fdf4; border-radius:6px; padding:8px 12px;">
                    <i class="bi bi-check-circle-fill"></i> All students have been assigned to groups.
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card-rupp">
            <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
                <i class="bi bi-people" style="font-size:40px; display:block; margin-bottom:12px; opacity:.4;"></i>
                <div style="font-size:15px; font-weight:500; color:#374151;">No groups yet</div>
                <div style="font-size:13px; margin-top:4px;">Create groups and assign students to them.</div>
                <button class="btn-rupp-primary" style="margin-top:16px;" onclick="openCreateModal()">
                    <i class="bi bi-plus-lg"></i> Create First Group
                </button>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Right: Unassigned students --}}
    <div class="card-rupp" style="position:sticky; top:20px;">
        <div style="padding:14px 16px; background:#f9fafb; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:13.5px; font-weight:600; color:#374151; display:flex; align-items:center; gap:8px;">
                <i class="bi bi-person-dash-fill" style="color:var(--rupp-gold);"></i>
                Unassigned
            </div>
            @if($unassigned->count() > 0)
            <span style="background:#fef3c7; color:#92400e; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:600;">
                {{ $unassigned->count() }}
            </span>
            @else
            <span style="background:#dcfce7; color:#166534; border-radius:20px; padding:2px 10px; font-size:12px; font-weight:600;">
                ✓ All assigned
            </span>
            @endif
        </div>
        <div style="max-height:400px; overflow-y:auto;">
            @forelse($unassigned as $student)
            <div style="padding:10px 16px; border-bottom:1px solid #f9fafb; display:flex; align-items:center; gap:10px;">
                <div style="width:30px; height:30px; border-radius:50%; background:#fdf3d7; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#92680a; flex-shrink:0;">
                    {{ strtoupper(substr($student->user->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:12.5px; font-weight:500; color:#374151;">{{ $student->user->name }}</div>
                    <div style="font-size:11px; color:#9ca3af; font-family:monospace;">{{ $student->student_id }}</div>
                </div>
            </div>
            @empty
            <div style="padding:20px; text-align:center; font-size:13px; color:#9ca3af;">
                <i class="bi bi-check-circle-fill" style="color:#16a34a; font-size:20px; display:block; margin-bottom:6px;"></i>
                All students assigned!
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Create Group Modal --}}
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:480px;">
        <div class="modal-content" style="border-radius:14px; overflow:hidden; border:none;">
            <div class="rupp-header-strip"><i class="bi bi-people-fill"></i><h5>Create New Group</h5></div>
            <div style="padding:24px;">
                <form method="POST" action="{{ route('teacher.groups.store', $section) }}">
                    @csrf

                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Group Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" class="form-control-rupp"
                            placeholder="e.g. Group 1, Alpha Team" required>
                    </div>

                    {{-- Optional: add initial members --}}
                    @if($unassigned->count() > 0)
                    <div style="margin-bottom:16px;">
                        <label class="form-label-rupp">Add Members (optional)</label>
                        <select name="initial_members[]" multiple class="form-select-rupp" style="height:120px; font-size:12px;">
                            @foreach($unassigned as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->user->name }} — {{ $student->student_id }}
                            </option>
                            @endforeach
                        </select>
                        <div style="font-size:11px; color:#9ca3af; margin-top:4px;">Hold Ctrl/Cmd to select multiple</div>
                    </div>
                    @endif

                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-rupp-primary">
                            <i class="bi bi-check-lg"></i> Create Group
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
function openCreateModal() {
    new bootstrap.Modal(document.getElementById('createGroupModal')).show();
}

function startEditName(groupId, currentName) {
    document.getElementById('name-display-' + groupId).style.display = 'none';
    document.getElementById('name-form-' + groupId).style.display = 'block';
    const input = document.getElementById('name-input-' + groupId);
    input.value = currentName;
    input.focus();
    input.select();
}

function cancelEditName(groupId) {
    document.getElementById('name-display-' + groupId).style.display = '';
    document.getElementById('name-form-' + groupId).style.display = 'none';
}
</script>
@endpush