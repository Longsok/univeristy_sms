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
    <button class="btn-rupp-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
        <i class="bi bi-plus-lg"></i> New Group
    </button>
</div>
 
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
 
    {{-- Groups --}}
    <div>
        @forelse($groups as $group)
        <div class="card-rupp" style="margin-bottom:16px;">
            <div class="card-rupp-header">
                <h5><i class="bi bi-people-fill" style="color:var(--rupp-gold)"></i> {{ $group->name }}</h5>
                <span class="badge-rupp badge-blue">{{ $group->members->count() }} members</span>
            </div>
            <div class="card-rupp-body">
                @if($group->members->count())
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                    @foreach($group->members as $member)
                    <div style="display:flex;align-items:center;gap:6px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:20px;padding:4px 10px;">
                        @if($member->role === 'leader')
                            <i class="bi bi-star-fill" style="color:var(--rupp-gold);font-size:11px;"></i>
                        @endif
                        <span style="font-size:12.5px;font-weight:{{ $member->role === 'leader' ? '600' : '400' }};">
                            {{ $member->student->user->name }}
                        </span>
                        <form action="{{ route('teacher.groups.assign', $group) }}" method="POST" style="display:inline;">
                            @csrf
                        </form>
                    </div>
                    @endforeach
                </div>
                @else
                <div style="font-size:13px;color:#9ca3af;margin-bottom:12px;">No members assigned yet.</div>
                @endif
 
                {{-- Assign students form --}}
                <form method="POST" action="{{ route('teacher.groups.assign', $group) }}"
                    style="display:flex;gap:8px;align-items:center;">
                    @csrf
                    <select name="student_ids[]" multiple class="form-select-rupp" style="flex:1;height:80px;">
                        @foreach($unassigned as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->user->name }} ({{ $student->student_id }})
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-rupp-outline" style="padding:6px 12px;font-size:12px;white-space:nowrap;">
                        <i class="bi bi-person-plus"></i> Assign
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="card-rupp">
            <div class="card-rupp-body" style="text-align:center;padding:40px;">
                <i class="bi bi-people" style="font-size:36px;color:#d1d5db;display:block;margin-bottom:10px;"></i>
                <div style="color:#6b7280;font-size:14px;">No groups created yet.</div>
            </div>
        </div>
        @endforelse
    </div>
 
    {{-- Unassigned students --}}
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5><i class="bi bi-person-dash" style="color:var(--rupp-gold)"></i> Unassigned</h5>
            <span class="badge-rupp badge-orange">{{ $unassigned->count() }}</span>
        </div>
        <div style="padding:0;">
            @forelse($unassigned as $student)
            <div style="padding:10px 16px;border-bottom:1px solid #f3f4f6;font-size:13px;">
                <div style="font-weight:500;">{{ $student->user->name }}</div>
                <div style="font-size:11px;color:#9ca3af;font-family:monospace;">{{ $student->student_id }}</div>
            </div>
            @empty
            <div style="padding:20px;text-align:center;font-size:13px;color:#9ca3af;">
                All students are assigned.
            </div>
            @endforelse
        </div>
    </div>
 
</div>
 
{{-- Add Group Modal --}}
<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none;">
            <div class="rupp-header-strip"><i class="bi bi-people-fill"></i><h5>Create New Group</h5></div>
            <div style="padding:24px;">
                <form method="POST" action="{{ route('teacher.groups.store', $section) }}">
                    @csrf
                    <div style="margin-bottom:16px;">
                        <label class="form-label-rupp">Group Name <span style="color:#ef4444">*</span></label>
                        <input type="text" name="name" class="form-control-rupp" placeholder="e.g. Group 1 or Alpha Team" required>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn-rupp-primary"><i class="bi bi-check-lg"></i> Create</button>
                        <button type="button" class="btn-rupp-outline" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
 