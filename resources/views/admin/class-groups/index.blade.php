@extends('layouts.admin')
@section('title', 'Class Groups')
@section('page-title', 'Class Groups')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Class Groups</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> / Class Groups
        </div>
    </div>
    <button class="btn-rupp-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg"></i> Add Class Group
    </button>
</div>

@if(session('success'))
<div class="alert-success-rupp" style="margin-bottom:16px;">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Groups by program --}}
@forelse($classGroups->groupBy(fn($g) => $g->program->name) as $programName => $groups)
<div style="margin-bottom:24px;">
    {{-- Program header --}}
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
        <div style="background:var(--rupp-green); color:#fff; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600;">
            <i class="bi bi-mortarboard-fill"></i> {{ $programName }}
        </div>
        <div style="height:1px; flex:1; background:#e5e7eb;"></div>
        <span style="font-size:12px; color:#9ca3af;">{{ $groups->count() }} groups</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:12px;">
        @foreach($groups->sortBy(['year_level','name']) as $group)
        <div class="card-rupp">
            {{-- Header --}}
            <div style="background:{{ $group->is_active ? 'var(--rupp-green)' : '#6b7280' }}; padding:14px 16px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="color:var(--rupp-gold); font-size:22px; font-weight:700; line-height:1;">
                        {{ $group->name }}
                    </div>
                    <div style="color:rgba(255,255,255,0.65); font-size:11px; margin-top:2px;">
                        Year {{ $group->year_level }} · {{ $group->program->code }}
                    </div>
                    @if($group->batch)
                    <div style="font-size:10px; color:var(--rupp-gold); margin-top:2px; font-weight:600; letter-spacing:.04em;">
                        Batch {{ $group->batch }}
                    </div>
                    @endif
                </div>
                <div style="text-align:right;">
                    <div style="font-size:24px; font-weight:700; color:#fff;">{{ $group->students_count }}</div>
                    <div style="font-size:10px; color:rgba(255,255,255,0.5);">/ {{ $group->capacity }} students</div>
                </div>
            </div>

            <div class="card-rupp-body" style="padding:12px 14px;">
                @if($group->description)
                <div style="font-size:12.5px; color:#6b7280; margin-bottom:10px;">{{ $group->description }}</div>
                @endif

                {{-- Capacity bar --}}
                <div style="margin-bottom:12px;">
                    <div style="height:6px; background:#f3f4f6; border-radius:3px; overflow:hidden;">
                        @php $pct = $group->capacity > 0 ? min(100, ($group->students_count / $group->capacity) * 100) : 0; @endphp
                        <div style="height:100%; width:{{ $pct }}%; background:{{ $pct >= 90 ? '#dc2626' : ($pct >= 70 ? '#f59e0b' : 'var(--rupp-green)') }}; border-radius:3px; transition:width .3s;"></div>
                    </div>
                    <div style="font-size:10.5px; color:#9ca3af; margin-top:3px;">
                        {{ $group->capacity - $group->students_count }} seats available
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex; gap:8px;">
                    <a href="{{ route('admin.class-groups.show', $group) }}"
                       class="btn-rupp-primary" style="padding:6px 12px; font-size:12px; flex:1; justify-content:center;">
                        <i class="bi bi-people-fill"></i> Manage
                    </a>
                    <button class="btn-icon edit" title="Edit"
                        onclick="openEdit({{ $group->id }}, '{{ $group->name }}', '{{ $group->description }}', {{ $group->year_level }}, {{ $group->batch ?? 0 }}, {{ $group->capacity }}, {{ $group->is_active ? 'true' : 'false' }})">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <form action="{{ route('admin.class-groups.destroy', $group) }}" method="POST"
                        onsubmit="return confirm('Delete {{ $group->name }}? Students will be unassigned.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon delete" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-people" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No class groups yet.</div>
        <div style="font-size:13px; margin-top:4px;">Create class groups like M1, M2, A1, A2 to organize students.</div>
        <button class="btn-rupp-primary" style="margin-top:16px;" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Add First Class Group
        </button>
    </div>
</div>
@endforelse

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none;">
            <div class="rupp-header-strip"><i class="bi bi-people-fill"></i><h5>Add New Class Group</h5></div>
            <div style="padding:24px;">
                <form method="POST" action="{{ route('admin.class-groups.store') }}">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Program <span style="color:#ef4444">*</span></label>
                        <select name="program_id" class="form-select-rupp" required>
                            <option value="">— Select Program —</option>
                            @foreach($programs->groupBy(fn($p) => $p->department->faculty->name) as $facName => $progs)
                                <optgroup label="{{ $facName }}">
                                    @foreach($progs as $prog)
                                    <option value="{{ $prog->id }}">{{ $prog->name }} ({{ $prog->code }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                        <div>
                            <label class="form-label-rupp">Group Name <span style="color:#ef4444">*</span></label>
                            <input type="text" name="name" class="form-control-rupp"
                                placeholder="e.g. M1, M2, A1, A2" required>
                            <div style="font-size:11px; color:#9ca3af; margin-top:3px;">
                                M = Morning, A = Afternoon, E = Evening
                            </div>
                        </div>
                        <div>
                            <label class="form-label-rupp">Year Level <span style="color:#ef4444">*</span></label>
                            <select name="year_level" class="form-select-rupp" required>
                                @for($i=1; $i<=6; $i++)
                                <option value="{{ $i }}">Year {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Description</label>
                        <input type="text" name="description" class="form-control-rupp"
                            placeholder="e.g. Morning Group 1 — Room 201">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Batch</label>
                        <input type="number" name="batch" value="{{ old('batch') }}"
                            class="form-control-rupp" placeholder="e.g. 1, 2, 3" min="1" max="999">
                        <div style="font-size:11px; color:#9ca3af; margin-top:3px;">
                            Batch 1 = first intake, Batch 2 = second intake...
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <label class="form-label-rupp">Capacity <span style="color:#ef4444">*</span></label>
                        <input type="number" name="capacity" value="40" class="form-control-rupp" min="1" max="200" required>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-rupp-primary"><i class="bi bi-check-lg"></i> Create</button>
                        <button type="button" class="btn-rupp-outline" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none;">
            <div class="rupp-header-strip"><i class="bi bi-pencil-fill"></i><h5>Edit Class Group</h5></div>
            <div style="padding:24px;">
                <form method="POST" action="" id="editForm">
                    @csrf @method('PUT')
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                        <div>
                            <label class="form-label-rupp">Group Name</label>
                            <input type="text" name="name" id="editName" class="form-control-rupp" required>
                        </div>
                        <div>
                            <label class="form-label-rupp">Year Level</label>
                            <select name="year_level" id="editYear" class="form-select-rupp">
                                @for($i=1; $i<=6; $i++)
                                <option value="{{ $i }}">Year {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Description</label>
                        <input type="text" name="description" id="editDesc" class="form-control-rupp">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Batch</label>
                        <input type="number" name="batch" id="editBatch" class="form-control-rupp" placeholder="e.g. 1, 2, 3" min="1" max="999">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Capacity</label>
                        <input type="number" name="capacity" id="editCap" class="form-control-rupp" min="1" max="200">
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                            <input type="checkbox" name="is_active" id="editActive" value="1" style="accent-color:var(--rupp-green);">
                            Active
                        </label>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-rupp-primary"><i class="bi bi-floppy-fill"></i> Update</button>
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
function openEdit(id, name, desc, year, batch, cap, active) {
    document.getElementById('editForm').action = `/admin/class-groups/${id}`;
    document.getElementById('editName').value    = name;
    document.getElementById('editDesc').value    = desc || '';
    document.getElementById('editYear').value    = year;
    document.getElementById('editBatch').value   = batch || '';
    document.getElementById('editCap').value     = cap;
    document.getElementById('editActive').checked = active;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush