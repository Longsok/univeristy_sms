<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::user()->student;

        // Get semester for this student's year level
        $currentSemester = Semester::forYearLevel($student->year_level);

        // Base query — all enrolled
        $query = $student->enrollments()
            ->with([
                'section.course.semester',
                'section.course.department',
                'section.teacher.user',
                'section.timetables',
                'grades.component',
            ])
            ->where('status', 'enrolled');

        $allEnrollments = $query->get();

        // Filter by current semester if available and not showing all
        $showAll = $request->boolean('all');

        if ($currentSemester && !$showAll) {
            $filtered = $allEnrollments->filter(fn($e) =>
                $e->section?->course?->semester_id === $currentSemester->id
            );
            // Fall back to all if no match
            $enrollments = $filtered->isNotEmpty() ? $filtered : $allEnrollments;
        } else {
            $enrollments = $allEnrollments;
        }

        return view('student.courses.index', compact(
            'enrollments',
            'currentSemester',
            'allEnrollments',
            'showAll'
        ));
    }
}