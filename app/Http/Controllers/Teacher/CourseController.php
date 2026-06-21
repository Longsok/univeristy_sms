<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        $teacher  = Auth::user()->teacher;

        $sections = $teacher->sections()
            ->with('course.department.faculty', 'course.semester', 'timetables')
            ->get();

        return view('teacher.courses.index', compact('sections'));
    }

    public function students(Section $section)
    {
        abort_unless(Auth::user()->teacher->id === $section->teacher_id, 403);

        $enrollments = $section->enrollments()
            ->with('student.user', 'student.program')
            ->where('status', 'enrolled')
            ->paginate(30);

        return view('teacher.courses.students', compact('section', 'enrollments'));
    }
}