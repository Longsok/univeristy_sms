<?php
namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptPdfService
{
    public function __construct(private GpaCalculatorService $gpa) {}

    /**
     * Generate and return a PDF response for download.
     */
    public function download(Student $student): \Illuminate\Http\Response
    {
        $student->load(
            'user',
            'program.department.faculty',
            'enrollments.section.course.semester',
            'enrollments.grades.component'
        );

        $gpa = $this->gpa->calculate($student);

        // Group enrollments by semester for the transcript layout
        $bySemester = collect($gpa['courses'])->groupBy('semester');

        $pdf = Pdf::loadView('student.transcript.pdf', [
            'student'    => $student,
            'gpa'        => $gpa,
            'bySemester' => $bySemester,
            'generatedAt'=> now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("transcript_{$student->student_id}.pdf");
    }

    /**
     * Return PDF as inline stream (for preview in browser).
     */
    public function stream(Student $student): \Illuminate\Http\Response
    {
        $student->load(
            'user',
            'program.department.faculty',
            'enrollments.section.course.semester',
            'enrollments.grades.component'
        );

        $gpa        = $this->gpa->calculate($student);
        $bySemester = collect($gpa['courses'])->groupBy('semester');

        $pdf = Pdf::loadView('student.transcript.pdf', [
            'student'    => $student,
            'gpa'        => $gpa,
            'bySemester' => $bySemester,
            'generatedAt'=> now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("transcript_{$student->student_id}.pdf");
    }
}