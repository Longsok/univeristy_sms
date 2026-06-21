@extends('layouts.student')
@section('title', 'Transcript')
@section('page-title', 'My Transcript')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Academic Transcript</h1>
        <div class="breadcrumb-text">{{ $student->program->name }}</div>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="{{ route('student.transcript.download') }}" class="btn-rupp-primary">
            <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
        </a>
        <a href="{{ route('student.transcript.print') }}" target="_blank" class="btn-rupp-outline">
            <i class="bi bi-printer-fill"></i> Print
        </a>
    </div>
</div>

{{-- GPA summary --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:28px;">
    <div style="background:var(--rupp-green);border-radius:12px;padding:20px;text-align:center;">
        <div style="font-size:11px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.06em;">
            Cumulative GPA
        </div>
        <div style="font-size:36px;font-weight:700;color:#fff;margin:4px 0;line-height:1;">
            {{ number_format($gpa['gpa'], 2) }}
        </div>
        <div style="font-size:11px;color:var(--rupp-gold);">out of 4.00</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon gold"><i class="bi bi-mortarboard"></i></div>
        <div class="stat-card-info">
            <div class="label">Credits Earned</div>
            <div class="value">{{ $gpa['total_credits'] }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="bi bi-book-fill"></i></div>
        <div class="stat-card-info">
            <div class="label">Courses Taken</div>
            <div class="value">{{ count($gpa['courses']) }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="bi bi-calendar3"></i></div>
        <div class="stat-card-info">
            <div class="label">Year Level</div>
            <div class="value">Year {{ $student->year_level }}</div>
        </div>
    </div>
</div>

{{-- Student info --}}
<div class="card-rupp" style="margin-bottom:20px;">
    <div class="card-rupp-header">
        <h5><i class="bi bi-person-fill" style="color:var(--rupp-gold)"></i> Student Information</h5>
    </div>
    <div class="card-rupp-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
            <div>
                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Student Name</div>
                <div style="font-size:14px;font-weight:600;color:#111827;margin-top:2px;">{{ $student->user->name }}</div>
                @if($student->user->name_kh)
                <div style="font-size:13px;color:#6b7280;font-family:'Hanuman',serif;">{{ $student->user->name_kh }}</div>
                @endif
            </div>
            <div>
                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Student ID</div>
                <div style="font-size:14px;font-weight:600;color:#111827;margin-top:2px;font-family:monospace;">{{ $student->student_id }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Program</div>
                <div style="font-size:14px;font-weight:600;color:#111827;margin-top:2px;">{{ $student->program->name }}</div>
                <div style="font-size:12px;color:#6b7280;">{{ $student->program->department->name }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Faculty</div>
                <div style="font-size:14px;font-weight:600;color:#111827;margin-top:2px;">{{ $student->program->department->faculty->name }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Year Level</div>
                <div style="font-size:14px;font-weight:600;color:#111827;margin-top:2px;">Year {{ $student->year_level }}</div>
            </div>
            @if($student->batch)
            <div>
                <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;">Batch</div>
                <div style="font-size:14px;font-weight:600;color:#111827;margin-top:2px;">
                    Batch {{ $student->batch }}
                </div>
                @if($student->classGroup)
                <div style="font-size:12px;color:#6b7280;">{{ $student->classGroup->name }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Course records grouped by semester --}}
@php
    $bySemester = collect($gpa['courses'])->groupBy('semester');
@endphp

@if($bySemester->count() > 0)
    @foreach($bySemester as $semesterName => $courses)
    @php
        $semCredits = collect($courses)->sum('credits');
        $semPoints  = collect($courses)->sum(fn($c) => ($c['grade_points'] ?? 0) * $c['credits']);
        $semGpa     = $semCredits > 0 ? round($semPoints / $semCredits, 2) : 0;
    @endphp
    <div class="card-rupp" style="margin-bottom:16px;">
        {{-- Semester header --}}
        <div style="background:var(--rupp-green); padding:12px 20px; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="color:var(--rupp-gold); font-size:11px; font-weight:600; letter-spacing:.06em; text-transform:uppercase;">
                    Semester
                </div>
                <div style="color:#fff; font-size:15px; font-weight:600; margin-top:2px;">
                    {{ $semesterName }}
                </div>
            </div>
            <div style="display:flex; gap:16px; text-align:center;">
                <div>
                    <div style="font-size:10px; color:rgba(255,255,255,.5); text-transform:uppercase;">Semester GPA</div>
                    <div style="font-size:20px; font-weight:700; color:var(--rupp-gold); line-height:1;">
                        {{ number_format($semGpa, 2) }}
                    </div>
                </div>
                <div>
                    <div style="font-size:10px; color:rgba(255,255,255,.5); text-transform:uppercase;">Credits</div>
                    <div style="font-size:20px; font-weight:700; color:#fff; line-height:1;">
                        {{ $semCredits }}
                    </div>
                </div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="table-rupp">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Course Name</th>
                        <th style="text-align:center;">Credits</th>
                        <th style="text-align:center;">Final Grade</th>
                        <th style="text-align:center;">Letter</th>
                        <th style="text-align:center;">Points</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                    <tr>
                        <td style="font-family:monospace;font-weight:600;color:var(--rupp-green);">
                            {{ $course['code'] }}
                        </td>
                        <td>{{ $course['course'] }}</td>
                        <td style="text-align:center;">{{ $course['credits'] }}</td>
                        <td style="text-align:center;font-weight:600;">
                            {{ number_format($course['final_grade'], 2) }}
                        </td>
                        <td style="text-align:center;">
                            <span class="badge-rupp {{ in_array($course['letter_grade'], ['A','A+','A-']) ? 'badge-green' : (in_array($course['letter_grade'], ['B+','B','B-','C+','C','C-','D']) ? 'badge-blue' : 'badge-red') }}">
                                {{ $course['letter_grade'] }}
                            </span>
                        </td>
                        <td style="text-align:center;">{{ $course['grade_points'] }}</td>
                        <td>
                            <span class="badge-rupp {{ match($course['grade_status']) {
                                'pass'       => 'badge-green',
                                'reexam'     => 'badge-orange',
                                'fail'       => 'badge-red',
                                'incomplete' => 'badge-gray',
                                default      => 'badge-gray'
                            } }}">
                                {{ ucfirst($course['grade_status']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    {{-- Cumulative GPA footer --}}
    <div class="card-rupp">
        <div style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div style="font-size:13px; color:#6b7280;">
                Total Credits Earned: <strong style="color:#111827;">{{ $gpa['total_credits'] }}</strong>
                &nbsp;·&nbsp;
                Total Courses: <strong style="color:#111827;">{{ count($gpa['courses']) }}</strong>
            </div>
            <div style="font-size:16px; font-weight:700; color:var(--rupp-green);">
                Cumulative GPA: {{ number_format($gpa['gpa'], 2) }} / 4.00
            </div>
        </div>
    </div>
@else
<div class="card-rupp">
    <div class="card-rupp-body" style="text-align:center;padding:48px;color:#9ca3af;">
        <i class="bi bi-file-earmark-x" style="font-size:40px;display:block;margin-bottom:12px;"></i>
        <div style="font-size:15px;font-weight:500;">No graded courses yet.</div>
        <div style="font-size:13px;margin-top:4px;">Your transcript will appear here after your teacher finalises grades.</div>
    </div>
</div>
@endif
@endsection