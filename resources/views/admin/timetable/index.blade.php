
@extends('layouts.admin')
@section('title', 'Timetable')
@section('page-title', 'Timetable Management')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $section->course->code }} — {{ $section->name }}</h1>
        <div class="breadcrumb-text">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a> /
            <a href="{{ route('admin.timetable.overview') }}">Timetable</a> /
            {{ $section->course->name }}
        </div>
    </div>
    <a href="{{ route('admin.timetable.overview') }}" class="btn-rupp-outline">
        <i class="bi bi-arrow-left"></i> Back to Overview
    </a>
</div>

{{-- Section info --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-body" style="padding:14px 20px;">
        <div style="display:flex; gap:24px; flex-wrap:wrap; font-size:13px;">
            <div style="display:flex; align-items:center; gap:8px; color:#6b7280;">
                <i class="bi bi-book-fill" style="color:var(--rupp-green);"></i>
                <span><strong>Course:</strong> {{ $section->course->name }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; color:#6b7280;">
                <i class="bi bi-collection-fill" style="color:var(--rupp-green);"></i>
                <span><strong>Section:</strong> {{ $section->name }}</span>
            </div>
            @if($section->course->program)
            <div style="display:flex; align-items:center; gap:8px; color:#6b7280;">
                <i class="bi bi-mortarboard-fill" style="color:var(--rupp-gold);"></i>
                <span><strong>Program:</strong> {{ $section->course->program->name }}</span>
            </div>
            @endif
            <div style="display:flex; align-items:center; gap:8px; color:#6b7280;">
                <i class="bi bi-person-workspace" style="color:var(--rupp-green);"></i>
                <span><strong>Teacher:</strong> {{ $section->teacher?->user?->name ?? 'Not assigned' }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; color:#6b7280;">
                <i class="bi bi-people-fill" style="color:var(--rupp-green);"></i>
                <span><strong>Students:</strong> {{ $section->enrollments->where('status','enrolled')->count() }} enrolled</span>
            </div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start;">

    {{-- Current timetable --}}
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5><i class="bi bi-calendar3-week-fill" style="color:var(--rupp-gold)"></i> Schedule</h5>
            <span class="badge-rupp badge-blue">{{ $section->timetables->count() }} {{ Str::plural('slot', $section->timetables->count()) }}</span>
        </div>

        @forelse($section->timetables->sortBy(fn($t) => array_search($t->day_of_week, $days)) as $timetable)
        <div style="padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:14px;">
                {{-- Day badge --}}
                <div style="width:80px; background:var(--rupp-green); color:#fff; border-radius:8px; padding:8px 0; text-align:center; flex-shrink:0;">
                    <div style="font-size:13px; font-weight:700;">{{ substr($timetable->day_of_week, 0, 3) }}</div>
                    <div style="font-size:10px; color:rgba(255,255,255,0.6);">{{ $timetable->day_of_week }}</div>
                </div>

                {{-- Time + room --}}
                <div>
                    <div style="font-size:15px; font-weight:600; color:#111827;">
                        {{ substr($timetable->start_time, 0, 5) }} — {{ substr($timetable->end_time, 0, 5) }}
                    </div>
                    @if($timetable->room)
                    <div style="font-size:12.5px; color:#6b7280; margin-top:3px;">
                        <i class="bi bi-geo-alt-fill" style="color:var(--rupp-gold);"></i>
                        Room {{ $timetable->room }}
                    </div>
                    @endif
                    @php
                        $start = \Carbon\Carbon::createFromTimeString($timetable->start_time);
                        $end   = \Carbon\Carbon::createFromTimeString($timetable->end_time);
                        $duration = $start->diffInMinutes($end);
                    @endphp
                    <div style="font-size:11px; color:#9ca3af; margin-top:2px;">
                        {{ $duration }} minutes
                    </div>
                </div>
            </div>

            {{-- Edit / Delete --}}
            <div style="display:flex; gap:8px; align-items:center;">
                <button class="btn-icon edit" title="Edit"
                    onclick="openEditModal({{ $timetable->id }}, '{{ $timetable->day_of_week }}', '{{ substr($timetable->start_time,0,5) }}', '{{ substr($timetable->end_time,0,5) }}', '{{ $timetable->room }}')">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <form action="{{ route('admin.timetable.destroy', $timetable) }}" method="POST"
                    onsubmit="return confirm('Remove {{ $timetable->day_of_week }} slot?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon delete" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="padding:48px; text-align:center; color:#9ca3af;">
            <i class="bi bi-calendar-x" style="font-size:36px; display:block; margin-bottom:12px;"></i>
            <div style="font-size:14px; font-weight:500;">No schedule yet</div>
            <div style="font-size:13px; margin-top:4px;">Use the form to add class days and times.</div>
        </div>
        @endforelse
    </div>

    {{-- Add slot form --}}
    <div class="card-rupp">
        <div class="rupp-header-strip">
            <i class="bi bi-plus-circle-fill"></i>
            <h5>Add Schedule Slot</h5>
        </div>
        <div class="card-rupp-body">
            <form method="POST" action="{{ route('admin.timetable.store', $section) }}">
                @csrf

                <div style="margin-bottom:14px;">
                    <label class="form-label-rupp">Day <span style="color:#ef4444">*</span></label>
                    <select name="day_of_week" class="form-select-rupp" required>
                        <option value="">— Select Day —</option>
                        @foreach($days as $day)
                            @php $taken = $section->timetables->pluck('day_of_week')->contains($day); @endphp
                            <option value="{{ $day }}" {{ $taken ? 'disabled' : '' }}
                                {{ old('day_of_week') === $day ? 'selected' : '' }}>
                                {{ $day }} {{ $taken ? '(already set)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('day_of_week')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label class="form-label-rupp">Start Time <span style="color:#ef4444">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time', '07:00') }}"
                            class="form-control-rupp" required>
                        @error('start_time')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label-rupp">End Time <span style="color:#ef4444">*</span></label>
                        <input type="time" name="end_time" value="{{ old('end_time', '09:00') }}"
                            class="form-control-rupp" required>
                        @error('end_time')<div class="form-error">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label class="form-label-rupp">Room / Location</label>
                    <input type="text" name="room" value="{{ old('room') }}"
                        class="form-control-rupp" placeholder="e.g. Room 101, Lab A, Building B">
                </div>

                <button type="submit" class="btn-rupp-primary" style="width:100%; justify-content:center;">
                    <i class="bi bi-plus-lg"></i> Add Slot
                </button>
            </form>

            {{-- Quick fill buttons --}}
            <div style="margin-top:16px; padding-top:14px; border-top:1px solid #f3f4f6;">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">Quick Fill</div>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    <button onclick="setTime('07:00','09:00')" class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">7:00–9:00</button>
                    <button onclick="setTime('09:00','11:00')" class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">9:00–11:00</button>
                    <button onclick="setTime('13:00','15:00')" class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">13:00–15:00</button>
                    <button onclick="setTime('15:00','17:00')" class="btn-rupp-outline" style="padding:4px 10px; font-size:11px;">15:00–17:00</button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none;">
            <div class="rupp-header-strip">
                <i class="bi bi-pencil-fill"></i>
                <h5>Edit Schedule Slot</h5>
            </div>
            <div style="padding:24px;">
                <form method="POST" action="" id="editForm">
                    @csrf @method('PUT')
                    <div style="margin-bottom:14px;">
                        <label class="form-label-rupp">Day</label>
                        <select name="day_of_week" id="editDay" class="form-select-rupp" required>
                            @foreach($days as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                        <div>
                            <label class="form-label-rupp">Start Time</label>
                            <input type="time" name="start_time" id="editStart" class="form-control-rupp" required>
                        </div>
                        <div>
                            <label class="form-label-rupp">End Time</label>
                            <input type="time" name="end_time" id="editEnd" class="form-control-rupp" required>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <label class="form-label-rupp">Room</label>
                        <input type="text" name="room" id="editRoom" class="form-control-rupp">
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
function setTime(start, end) {
    document.querySelector('input[name="start_time"]').value = start;
    document.querySelector('input[name="end_time"]').value   = end;
}

function openEditModal(id, day, start, end, room) {
    document.getElementById('editForm').action = `/admin/timetable/${id}`;
    document.getElementById('editDay').value   = day;
    document.getElementById('editStart').value = start;
    document.getElementById('editEnd').value   = end;
    document.getElementById('editRoom').value  = room || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush