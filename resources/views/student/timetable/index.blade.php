@extends('layouts.student')
@section('title', 'Timetable')
@section('page-title', 'My Timetable')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Weekly Timetable</h1>
        <div class="breadcrumb-text">Your class schedule for this semester</div>
    </div>
</div>

@php
// Color palette per course
$palette = [
    ['bg'=>'#dcfce7','border'=>'#86efac','text'=>'#166534','header'=>'#1e4d2b'],
    ['bg'=>'#dbeafe','border'=>'#93c5fd','text'=>'#1e40af','header'=>'#1d4ed8'],
    ['bg'=>'#fdf3d7','border'=>'#fcd34d','text'=>'#92680a','header'=>'#c9a227'],
    ['bg'=>'#f3e8ff','border'=>'#d8b4fe','text'=>'#6b21a8','header'=>'#7c3aed'],
    ['bg'=>'#ffedd5','border'=>'#fdba74','text'=>'#9a3412','header'=>'#ea580c'],
    ['bg'=>'#e0f2fe','border'=>'#7dd3fc','text'=>'#075985','header'=>'#0369a1'],
    ['bg'=>'#fce7f3','border'=>'#f9a8d4','text'=>'#9d174d','header'=>'#be185d'],
];

// Assign a color to each course code
$courseColors = [];
$pi = 0;
foreach($timetable as $day => $slots) {
    foreach($slots as $slot) {
        if (!isset($courseColors[$slot['code']])) {
            $courseColors[$slot['code']] = $palette[$pi % count($palette)];
            $pi++;
        }
    }
}

// Time grid — each row = 30 minutes
$timeSlots = [];
for ($h = 7; $h <= 19; $h++) {
    $timeSlots[] = sprintf('%02d:00', $h);
    $timeSlots[] = sprintf('%02d:30', $h);
}

// Helper: convert HH:MM to minutes since midnight
function toMinutes(string $time): int {
    [$h, $m] = explode(':', $time);
    return (int)$h * 60 + (int)$m;
}

// Helper: how many 30-min rows does this slot span?
function spanRows(string $start, string $end): int {
    $diff = toMinutes($end) - toMinutes($start);
    return max(1, (int)round($diff / 30));
}
@endphp

<div class="card-rupp" style="overflow-x:auto; margin-bottom:20px;">
    <div style="min-width:700px;">
        {{-- Use CSS grid: 1 col for time + 7 cols for days --}}
        {{-- Each row = 30 minutes = 48px height --}}

        {{-- HEADER ROW --}}
        <div style="display:grid; grid-template-columns:80px repeat(7, 1fr); position:sticky; top:0; z-index:10;">
            <div style="background:#f9fafb; border-bottom:2px solid var(--rupp-green); padding:12px 8px; font-size:10px; font-weight:600; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em;">
                TIME
            </div>
            @foreach($days as $day)
            <div style="background:{{ in_array($day, ['Saturday','Sunday']) ? '#f3f4f6' : '#f9fafb' }}; border-bottom:2px solid var(--rupp-green); border-left:1px solid #e5e7eb; padding:12px 8px; font-size:13px; font-weight:700; color:{{ in_array($day, ['Saturday','Sunday']) ? '#9ca3af' : 'var(--rupp-green)' }}; text-align:center;">
                {{ substr($day, 0, 3) }}
                <div style="font-size:9px; font-weight:400; color:#9ca3af; margin-top:1px;">{{ $day }}</div>
            </div>
            @endforeach
        </div>

        {{-- BODY: position slots absolutely within a relative container --}}
        <div style="display:grid; grid-template-columns:80px repeat(7, 1fr); position:relative;">

            {{-- TIME LABELS + horizontal lines --}}
            @foreach($timeSlots as $idx => $slot)
            @php $isHour = str_ends_with($slot, ':00'); @endphp

            {{-- Time label column --}}
            <div style="
                height:48px;
                display:flex;
                align-items:center;
                padding:0 8px;
                background:#fafafa;
                border-bottom:1px solid {{ $isHour ? '#e5e7eb' : '#f3f4f6' }};
                font-size:{{ $isHour ? '11' : '10' }}px;
                color:{{ $isHour ? '#6b7280' : '#d1d5db' }};
                font-weight:{{ $isHour ? '500' : '400' }};
            ">
                {{ $isHour ? $slot : '' }}
            </div>

            {{-- 7 day columns for this row --}}
            @foreach($days as $day)
            @php
                // Check if a slot STARTS at this time on this day
                $slotHere = collect($timetable[$day])->first(
                    fn($s) => substr($s['start_time'], 0, 5) === $slot
                );

                // Check if a slot is ONGOING (started before, hasn't ended yet)
                // so we can skip rendering a cell (the card spans over it)
                $covered = collect($timetable[$day])->first(function($s) use ($slot) {
                    $slotMin  = toMinutes($slot);
                    $startMin = toMinutes(substr($s['start_time'], 0, 5));
                    $endMin   = toMinutes(substr($s['end_time'], 0, 5));
                    return $slotMin > $startMin && $slotMin < $endMin;
                });
            @endphp

            @if($slotHere)
            @php
                $c    = $courseColors[$slotHere['code']] ?? $palette[0];
                $rows = spanRows(substr($slotHere['start_time'],0,5), substr($slotHere['end_time'],0,5));
                $cardHeight = ($rows * 48) - 4; // 48px per row minus small gap
            @endphp
            {{-- Render the card spanning multiple rows --}}
            <div style="
                grid-row: span {{ $rows }};
                border-left:1px solid #e5e7eb;
                padding:4px;
                background:{{ in_array($day, ['Saturday','Sunday']) ? '#fafafa' : '#fff' }};
                position:relative;
            ">
                <div style="
                    background:{{ $c['bg'] }};
                    border:1px solid {{ $c['border'] }};
                    border-left:3px solid {{ $c['header'] }};
                    border-radius:6px;
                    padding:6px 8px;
                    height:{{ $cardHeight }}px;
                    overflow:hidden;
                    display:flex;
                    flex-direction:column;
                    justify-content:flex-start;
                ">
                    <div style="font-size:11px; font-weight:700; color:{{ $c['header'] }};">
                        {{ $slotHere['code'] }}
                    </div>
                    <div style="font-size:10.5px; color:{{ $c['text'] }}; margin-top:2px; line-height:1.3;">
                        {{ $slotHere['course'] }}
                    </div>
                    @if($rows >= 3)
                    <div style="font-size:10px; color:#9ca3af; margin-top:4px;">
                        <i class="bi bi-clock" style="font-size:9px;"></i>
                        {{ substr($slotHere['start_time'],0,5) }}–{{ substr($slotHere['end_time'],0,5) }}
                    </div>
                    @endif
                    @if($slotHere['room'] && $rows >= 2)
                    <div style="font-size:10px; color:#9ca3af; margin-top:2px;">
                        <i class="bi bi-geo-alt-fill" style="font-size:9px;"></i>
                        {{ $slotHere['room'] }}
                    </div>
                    @endif
                    @if($rows >= 4)
                    <div style="font-size:10px; color:#9ca3af; margin-top:2px;">
                        <i class="bi bi-person-fill" style="font-size:9px;"></i>
                        {{ $slotHere['teacher'] }}
                    </div>
                    @endif
                </div>
            </div>

            @elseif($covered)
            {{-- This cell is covered by a spanning card above — skip it --}}
            {{-- CSS grid handles this automatically with grid-row: span --}}

            @else
            {{-- Empty cell --}}
            <div style="
                height:48px;
                border-left:1px solid #e5e7eb;
                border-bottom:1px solid {{ $isHour ? '#e5e7eb' : '#f3f4f6' }};
                background:{{ in_array($day, ['Saturday','Sunday']) ? '#fafafa' : '#fff' }};
            "></div>
            @endif

            @endforeach
            @endforeach

        </div>
    </div>
</div>

{{-- Legend --}}
@php $allSlots = collect($timetable)->flatten(1)->unique('code'); @endphp
@if($allSlots->count())
<div class="card-rupp">
    <div class="card-rupp-header">
        <h5><i class="bi bi-book-fill" style="color:var(--rupp-gold)"></i> My Courses This Semester</h5>
    </div>
    <div class="card-rupp-body">
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            @foreach($allSlots as $slot)
            @php $c = $courseColors[$slot['code']] ?? $palette[0]; @endphp
            <div style="background:{{ $c['bg'] }}; border:1px solid {{ $c['border'] }}; border-left:3px solid {{ $c['header'] }}; border-radius:8px; padding:10px 14px; min-width:200px;">
                <div style="font-size:12px; font-weight:700; color:{{ $c['header'] }};">{{ $slot['code'] }}</div>
                <div style="font-size:12px; color:{{ $c['text'] }}; margin-top:2px;">{{ $slot['course'] }}</div>
                <div style="font-size:11px; color:#9ca3af; margin-top:3px;">
                    <i class="bi bi-person-fill"></i> {{ $slot['teacher'] }}
                </div>
                @if($slot['room'])
                <div style="font-size:11px; color:#9ca3af; margin-top:1px;">
                    <i class="bi bi-geo-alt-fill"></i> {{ $slot['room'] }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center; padding:48px; color:#9ca3af;">
        <i class="bi bi-calendar-x" style="font-size:40px; display:block; margin-bottom:12px;"></i>
        <div style="font-size:15px; font-weight:500;">No schedule yet</div>
        <div style="font-size:13px; margin-top:4px;">
            Your timetable will appear here once your teacher sets up the class schedule.
        </div>
    </div>
</div>
@endif
@endsection