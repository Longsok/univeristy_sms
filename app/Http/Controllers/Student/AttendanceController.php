<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{AttendanceRecord, Section};
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function records()
    {
        $student = Auth::user()->student;

        if (!$student) {
            abort(403, 'No student profile found.');
        }

        // Get all sections the student is enrolled in
        $enrollments = $student->enrollments()
            ->where('status', 'enrolled')
            ->with('section.course.program', 'section.teacher.user')
            ->get();

        // For each section, get attendance records
        $attendanceData = $enrollments->map(function ($enrollment) use ($student) {
            $section = $enrollment->section;

            $records = AttendanceRecord::where('section_id', $section->id)
                ->where('student_id', $student->id)
                ->get()
                ->keyBy('week');

            $counts = [
                'present'    => $records->where('status', 'present')->count(),
                'late'       => $records->where('status', 'late')->count(),
                'permission' => $records->where('status', 'permission')->count(),
                'absent'     => $records->where('status', 'absent')->count(),
                'total'      => $records->count(),
            ];

            $score = AttendanceRecord::calculateScore($records->values()->all());

            return [
                'section' => $section,
                'records' => $records,  // keyed by week
                'counts'  => $counts,
                'score'   => $score,
            ];
        });

        $weeks = range(1, 16);

        return view('student.attendance.records', compact('attendanceData', 'weeks'));
    }
}