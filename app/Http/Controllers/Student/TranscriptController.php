<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\GpaCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class TranscriptController extends Controller
{
    public function __construct(private GpaCalculatorService $gpa) {}

    public function index()
    {
        $student = Auth::user()->student;

        if (!$student) {
            abort(403, 'No student profile found.');
        }

        $student->load(
            'user',
            'program.department.faculty',
            'enrollments.section.course.semester',
            'enrollments.grades.component'
        );

        $gpa = $this->gpa->calculate($student);

        return view('student.transcript.index', compact('student', 'gpa'));
    }

    public function download()
    {
        $student = Auth::user()->student;

        if (!$student) {
            abort(403, 'No student profile found.');
        }

        $student->load(
            'user',
            'program.department.faculty',
            'enrollments.section.course.semester',
            'enrollments.grades.component'
        );

        $gpa         = $this->gpa->calculate($student);
        $bySemester  = collect($gpa['courses'])->groupBy('semester');
        $generatedAt = now()->format('d M Y, H:i');

        $pdf = Pdf::loadView('student.transcript.pdf', compact(
            'student', 'gpa', 'bySemester', 'generatedAt'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("transcript_{$student->student_id}.pdf");
    }

    public function print()
    {
        $student = Auth::user()->student;

        if (!$student) {
            abort(403, 'No student profile found.');
        }

        $student->load(
            'user',
            'program.department.faculty',
            'enrollments.section.course.semester',
            'enrollments.grades.component'
        );

        $gpa         = $this->gpa->calculate($student);
        $bySemester  = collect($gpa['courses'])->groupBy('semester');
        $generatedAt = now()->format('d M Y, H:i');

        // Opens PDF view in browser — user presses Ctrl+P to print
        return view('student.transcript.pdf', compact(
            'student', 'gpa', 'bySemester', 'generatedAt'
        ));
    }
}