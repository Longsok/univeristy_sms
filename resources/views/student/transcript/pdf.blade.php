<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transcript — {{ $student->student_id }}</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; background: #fff; }
 
        .header { background: #1e4d2b; padding: 20px 24px; display: flex; align-items: center; gap: 16px; }
        .header-text h1 { color: #c9a227; font-size: 16px; font-weight: bold; }
        .header-text p { color: rgba(255,255,255,0.7); font-size: 10px; margin-top: 2px; }
 
        .gold-bar { background: #c9a227; height: 4px; }
 
        .content { padding: 20px 24px; }
 
        .student-info { display: flex; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #e5e7eb; }
        .info-group { }
        .info-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
        .info-value { font-size: 13px; font-weight: bold; color: #111827; }
        .info-value-sm { font-size: 11px; color: #374151; }
 
        .gpa-box { background: #1e4d2b; color: #fff; padding: 12px 20px; border-radius: 8px; text-align: center; min-width: 100px; }
        .gpa-label { font-size: 9px; color: rgba(255,255,255,0.6); text-transform: uppercase; }
        .gpa-value { font-size: 28px; font-weight: bold; color: #c9a227; line-height: 1; margin: 4px 0; }
        .gpa-sub { font-size: 9px; color: rgba(255,255,255,0.5); }
 
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin: 16px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #e5e7eb; }
 
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f9fafb; padding: 7px 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; border-bottom: 1px solid #e5e7eb; text-align: left; }
        td { padding: 8px 10px; font-size: 10.5px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        tr:last-child td { border-bottom: none; }
 
        .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 9px; font-weight: bold; }
        .badge-pass { background: #dcfce7; color: #166534; }
        .badge-reexam { background: #ffedd5; color: #c2410c; }
        .badge-fail { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f3f4f6; color: #6b7280; }
 
        .footer { margin-top: 24px; padding-top: 16px; border-top: 2px solid #e5e7eb; display: flex; justify-content: space-between; font-size: 9px; color: #9ca3af; }
 
        .signature-box { border-top: 1px solid #374151; padding-top: 6px; text-align: center; width: 160px; font-size: 10px; color: #374151; }
    </style>
</head>
<body>
 
{{-- Header --}}
<div class="header">
    <div class="header-text">
        <h1>Royal University of Phnom Penh</h1>
        <p>Official Academic Transcript</p>
    </div>
</div>
<div class="gold-bar"></div>
 
<div class="content">
 
    {{-- Student info + GPA --}}
    <div class="student-info">
        <div>
            <div class="info-label">Student Name</div>
            <div class="info-value">{{ $student->user->name }}</div>
 
            <div style="margin-top:10px;">
                <div class="info-label">Student ID</div>
                <div class="info-value-sm" style="font-family:monospace;">{{ $student->student_id }}</div>
            </div>
 
            <div style="margin-top:10px;">
                <div class="info-label">Program</div>
                <div class="info-value-sm">{{ $student->program->name }}</div>
                <div style="font-size:10px; color:#9ca3af;">{{ $student->program->department->name }} — {{ $student->program->department->faculty->name }}</div>
            </div>
        </div>
 
        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
            <div class="gpa-box">
                <div class="gpa-label">Cumulative GPA</div>
                <div class="gpa-value">{{ number_format($gpa['gpa'], 2) }}</div>
                <div class="gpa-sub">out of 4.00</div>
            </div>
            <div style="text-align:right;">
                <div class="info-label">Year Level</div>
                <div class="info-value-sm">Year {{ $student->year_level }}</div>
            </div>
            <div style="text-align:right;">
                <div class="info-label">Total Credits</div>
                <div class="info-value-sm">{{ $gpa['total_credits'] }} credits</div>
            </div>
        </div>
    </div>
 
    {{-- Courses by semester --}}
    @foreach($bySemester as $semesterName => $courses)
    <div class="section-title">{{ $semesterName }}</div>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Course Name</th>
                <th style="text-align:center;">Credits</th>
                <th style="text-align:center;">Final Grade</th>
                <th style="text-align:center;">Letter</th>
                <th style="text-align:center;">Points</th>
                <th style="text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
            <tr>
                <td style="font-family:monospace; font-weight:bold; color:#1e4d2b;">{{ $course['code'] }}</td>
                <td>{{ $course['course'] }}</td>
                <td style="text-align:center;">{{ $course['credits'] }}</td>
                <td style="text-align:center; font-weight:bold;">{{ number_format($course['final_grade'], 2) }}</td>
                <td style="text-align:center;">
                    <span class="badge {{ in_array($course['letter_grade'], ['A+','A','A-']) ? 'badge-pass' : (in_array($course['letter_grade'], ['B+','B','B-','C+','C','C-','D']) ? 'badge-gray' : 'badge-fail') }}">
                        {{ $course['letter_grade'] }}
                    </span>
                </td>
                <td style="text-align:center;">{{ $course['grade_points'] }}</td>
                <td style="text-align:center;">
                    <span class="badge {{ match($course['grade_status']) {
                        'pass'   => 'badge-pass',
                        'reexam' => 'badge-reexam',
                        'fail'   => 'badge-fail',
                        default  => 'badge-gray'
                    } }}">
                        {{ ucfirst($course['grade_status']) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach
 
    {{-- Footer --}}
    <div class="footer">
        <div>
            <div>Generated: {{ $generatedAt }}</div>
            <div style="margin-top:2px;">Royal University of Phnom Penh — Registrar's Office</div>
        </div>
        <div class="signature-box">
            Registrar's Signature
        </div>
    </div>
 
</div>
</body>
</html>