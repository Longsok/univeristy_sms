<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Semester, Announcement};
use App\Services\GpaCalculatorService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private GpaCalculatorService $gpa) {}

    public function index()
    {
        $student = Auth::user()->student;

        if (!$student) {
            abort(403, 'No student profile found. Contact the administrator.');
        }

        $student->load(
            'program.department.faculty',
            'classGroup',
            'enrollments.section.course.semester',
            'enrollments.grades'
        );

        // Get the current semester for this student's year level
        $currentSemester = Semester::forYearLevel($student->year_level);

        // Current semester enrollments only
        $currentEnrollments = $student->enrollments()
            ->where('status', 'enrolled')
            ->with([
                'section.course.semester',
                'section.teacher.user',
                'grades.component',
            ])
            ->get()
            ->filter(function ($enrollment) use ($currentSemester) {
                if (!$currentSemester) return true; // show all if no semester set
                $semesterId = $enrollment->section?->course?->semester_id;
                return $semesterId === $currentSemester->id;
            });

        // If no current semester match, fall back to all enrolled
        if ($currentEnrollments->isEmpty()) {
            $currentEnrollments = $student->enrollments()
                ->where('status', 'enrolled')
                ->with([
                    'section.course.semester',
                    'section.teacher.user',
                    'grades.component',
                ])
                ->get();
        }

        return view('student.dashboard', [
            'student'           => $student,
            'gpa'               => $this->gpa->calculate($student),
            'enrollments'       => $currentEnrollments,
            'currentSemester'   => $currentSemester,
            'announcements'     => Announcement::published()
                                    ->forRole('student')
                                    ->latest('published_at')
                                    ->take(5)->get(),
        ]);
    }
}