@extends('layouts.student')
@section('title', 'My Dashboard')
@section('page-title', 'My Dashboard')

@section('content')
{{-- Current semester indicator --}}
@if(isset($currentSemester) && $currentSemester)
<div style="background:{{ $currentSemester->isRunning() ? '#f0fdf4' : '#eff6ff' }}; border:1px solid {{ $currentSemester->isRunning() ? '#86efac' : '#bfdbfe' }}; border-radius:10px; padding:10px 16px; margin-bottom:16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
    <i class="bi bi-calendar-check-fill" style="color:{{ $currentSemester->isRunning() ? '#16a34a' : '#2563eb' }}; font-size:16px;"></i>
    <div style="font-size:13px; color:{{ $currentSemester->isRunning() ? '#166534' : '#1e40af' }};">
        <strong>{{ $currentSemester->isRunning() ? 'Current Semester' : 'Next Semester' }}:</strong>
        {{ $currentSemester->name }} {{ $currentSemester->academic_year }}
        @if($currentSemester->year_level) · Year {{ $currentSemester->year_level }} @endif
        @if($currentSemester->isRunning())
        · {{ $currentSemester->start_date->format('d M') }} – {{ $currentSemester->end_date->format('d M Y') }}
        · <strong>{{ $currentSemester->progress }}% complete</strong>
        @else
        · Starts {{ $currentSemester->start_date->format('d M Y') }}
        @endif
    </div>
</div>
@endif
<div class="page-header">
    <div class="page-header-left">
        <h1>Hello, {{ explode(' ', Auth::user()->name)[0] }}</h1>
        <div class="breadcrumb-text">
            {{ $student->program->name }} — Year {{ $student->year_level }}
        </div>
    </div>
</div>

{{-- GPA + stats — clickable --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:16px; margin-bottom:28px;">

    {{-- GPA card — links to transcript --}}
    <a href="{{ route('student.transcript.index') }}"
       style="background:var(--rupp-green); border-radius:12px; padding:20px; display:flex; align-items:center; gap:14px; text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.15)'"
       onmouseout="this.style.boxShadow=''">
        <div style="width:48px;height:48px;border-radius:10px;background:rgba(201,162,39,0.2);display:flex;align-items:center;justify-content:center;font-size:22px;color:var(--rupp-gold);">
            <i class="bi bi-award-fill"></i>
        </div>
        <div>
            <div style="font-size:12px; color:rgba(255,255,255,0.65);">Current GPA</div>
            <div style="font-size:28px; font-weight:700; color:#fff; line-height:1;">{{ number_format($gpa['gpa'], 2) }}</div>
        </div>
    </a>

    {{-- Enrolled Courses — links to courses --}}
    <a href="{{ route('student.courses.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon green"><i class="bi bi-book-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Enrolled Courses</div>
            <div class="value">{{ $enrollments->count() }}</div>
        </div>
    </a>

    {{-- Credits Earned — links to transcript --}}
    <a href="{{ route('student.transcript.index') }}" class="stat-card"
       style="text-decoration:none; transition:box-shadow .15s; cursor:pointer;"
       onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
       onmouseout="this.style.boxShadow=''">
        <div class="stat-card-icon gold"><i class="bi bi-award"></i></div>
        <div class="stat-card-info">
            <div class="label">Credits Earned</div>
            <div class="value">{{ $gpa['total_credits'] }}</div>
        </div>
    </a>

</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px;" class="responsive-grid-2">
    {{-- Course list --}}
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5><i class="bi bi-book-fill" style="color:var(--rupp-gold)"></i> My Courses</h5>
            <a href="{{ route('student.courses.index') }}" class="btn-rupp-outline" style="font-size:12px; padding:5px 12px;">View All</a>
        </div>
        <div style="padding:0;">
            @forelse($enrollments as $enrollment)
            <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-weight:600; font-size:13.5px; color:var(--rupp-green);">
                        {{ $enrollment->section->course->code }}
                    </div>
                    <div style="font-size:13px; color:#374151;">{{ $enrollment->section->course->name }}</div>
                    <div style="font-size:12px; color:#9ca3af;">
                        {{ $enrollment->section->teacher?->user?->name ?? 'No teacher assigned' }}
                    </div>
                </div>
                <div style="text-align:right;">
                    @if($enrollment->grade_status && $enrollment->grade_status !== 'not_graded')
                        <span class="badge-rupp {{ match($enrollment->grade_status) {
                            'pass'   => 'badge-green',
                            'reexam' => 'badge-orange',
                            'fail'   => 'badge-red',
                            default  => 'badge-gray'
                        } }}">
                            {{ $enrollment->letter_grade ?? ucfirst($enrollment->grade_status) }}
                        </span>
                    @else
                        <span class="badge-rupp badge-gray">In progress</span>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:24px; text-align:center; color:#9ca3af; font-size:13px;">
                Not enrolled in any courses yet.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Announcements --}}
    <div class="card-rupp">
        <div class="card-rupp-header">
            <h5><i class="bi bi-megaphone-fill" style="color:var(--rupp-gold)"></i> Notices</h5>
        </div>
        <div style="padding:0;">
            @forelse($announcements as $ann)
            <div style="padding:12px 16px; border-bottom:1px solid #f3f4f6;">
                <div style="font-size:13px; font-weight:600; color:#111827;">{{ $ann->title }}</div>
                <div style="font-size:12px; color:#6b7280; margin-top:2px;">{{ Str::limit($ann->body, 80) }}</div>
                <div style="font-size:11px; color:#9ca3af; margin-top:4px;">{{ $ann->published_at->diffForHumans() }}</div>
            </div>
            @empty
            <div style="padding:24px; text-align:center; color:#9ca3af; font-size:13px;">No announcements</div>
            @endforelse
        </div>
    </div>
</div>
@endsection