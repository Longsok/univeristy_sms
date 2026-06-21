<?php
// app/Http/Controllers/Teacher/AttendanceController.php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{AttendanceRecord, Section};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected int $totalWeeks = 16;

    /**
     * Show the weekly attendance grid for a section
     */
    public function index(Section $section)
    {
        $teacher = Auth::user()->teacher;

        abort_unless($teacher && $teacher->id === $section->teacher_id, 403,
            'You are not assigned to this section.');

        $section->load('course', 'enrollments.student.user');

        // Get all attendance records for this section
        $records = AttendanceRecord::where('section_id', $section->id)
            ->get()
            ->groupBy('student_id')
            ->map(fn($recs) => $recs->keyBy('week'));

        // Build student list with their weekly records
        $students = $section->enrollments
            ->where('status', 'enrolled')
            ->map(function ($enrollment) use ($records) {
                $student     = $enrollment->student;
                $weekRecords = $records->get($student->id, collect());

                // Count each status
                $counts = [
                    'present'    => $weekRecords->where('status', 'present')->count(),
                    'late'       => $weekRecords->where('status', 'late')->count(),
                    'permission' => $weekRecords->where('status', 'permission')->count(),
                    'absent'     => $weekRecords->where('status', 'absent')->count(),
                ];

                // Score out of 10
                $score = AttendanceRecord::calculateScore($weekRecords->values()->all());

                return [
                    'student'     => $student,
                    'weekRecords' => $weekRecords, // keyed by week number
                    'counts'      => $counts,
                    'score'       => $score,
                ];
            });

        $weeks = range(1, $this->totalWeeks);

        return view('teacher.attendance.index', compact('section', 'students', 'weeks'));
    }

    /**
     * Save attendance for one student for one week
     * Called via AJAX when teacher clicks a cell
     */
    public function save(Request $request, Section $section)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->id === $section->teacher_id, 403);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'week'       => 'required|integer|min:1|max:16',
            'status'     => 'required|in:present,absent,late,permission',
        ]);

        AttendanceRecord::updateOrCreate(
            [
                'section_id' => $section->id,
                'student_id' => $data['student_id'],
                'week'       => $data['week'],
            ],
            ['status' => $data['status']]
        );

        // Return updated counts for this student
        $records = AttendanceRecord::where('section_id', $section->id)
            ->where('student_id', $data['student_id'])
            ->get();

        return response()->json([
            'success' => true,
            'counts'  => [
                'present'    => $records->where('status', 'present')->count(),
                'late'       => $records->where('status', 'late')->count(),
                'permission' => $records->where('status', 'permission')->count(),
                'absent'     => $records->where('status', 'absent')->count(),
            ],
            'score' => AttendanceRecord::calculateScore($records->all()),
        ]);
    }

    /**
     * Save entire week for all students at once (bulk)
     */
    public function saveWeek(Request $request, Section $section)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->id === $section->teacher_id, 403);

        $data = $request->validate([
            'week'       => 'required|integer|min:1|max:16',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status'     => 'required|in:present,absent,late,permission',
        ]);

        foreach ($data['attendance'] as $row) {
            AttendanceRecord::updateOrCreate(
                [
                    'section_id' => $section->id,
                    'student_id' => $row['student_id'],
                    'week'       => $data['week'],
                ],
                ['status' => $row['status']]
            );
        }

        return back()->with('success', "Week {$data['week']} attendance saved.");
    }
}