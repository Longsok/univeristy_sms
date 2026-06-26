<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Section, Enrollment};
use Illuminate\Http\Request;

class AdminGradeController extends Controller
{
    /**
     * Show all sections with finalised grade status
     * Admin can unlock grades per section
     */
    public function index()
    {
        $sections = Section::with([
            'course.program.department',
            'teacher.user',
            'enrollments',
        ])
        ->withCount([
            'enrollments as total_students'    => fn($q) => $q->where('status','enrolled'),
            'enrollments as finalised_count'   => fn($q) => $q->where('grades_finalised', true),
            'enrollments as unfinalised_count' => fn($q) => $q->where('grades_finalised', false)->where('status','enrolled'),
        ])
        ->orderByDesc('created_at')
        ->get();

        return view('admin.grades.index', compact('sections'));
    }

    /**
     * Unlock grades for an entire section
     * Resets grades_finalised to false for all enrollments in section
     */
    public function unlock(Section $section)
    {
        $count = $section->enrollments()
            ->where('grades_finalised', true)
            ->update([
                'grades_finalised'    => false,
                'grades_finalised_at' => null,
            ]);

        return back()->with('success',
            "Grades unlocked for section {$section->name} — {$count} enrollment(s) can now be edited by the teacher."
        );
    }

    /**
     * Unlock grades for a single student enrollment
     */
    public function unlockOne(Enrollment $enrollment)
    {
        $enrollment->update([
            'grades_finalised'    => false,
            'grades_finalised_at' => null,
        ]);

        return back()->with('success',
            "Grades unlocked for {$enrollment->student->user->name}."
        );
    }
}