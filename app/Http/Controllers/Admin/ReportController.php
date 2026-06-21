<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Section, Student};
use App\Exports\{GradesExport, AttendanceExport};
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function gradesExcel(Section $section)
    {
        return Excel::download(new GradesExport($section), "grades_section_{$section->id}.xlsx");
    }

    public function attendanceExcel(Section $section)
    {
        return Excel::download(new AttendanceExport($section), "attendance_section_{$section->id}.xlsx");
    }

    public function transcriptPdf(Student $student)
    {
        $student->load('enrollments.section.course.semester', 'enrollments.grades.component', 'program.department.faculty');
        $pdf = Pdf::loadView('student.transcript.pdf', compact('student'));
        return $pdf->download("transcript_{$student->student_id}.pdf");
    }
}