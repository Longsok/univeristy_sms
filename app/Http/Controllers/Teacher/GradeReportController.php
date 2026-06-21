<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Exports\GradeReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class GradeReportController extends Controller
{
    /**
     * Show the grade status report for a section.
     * Groups students by grade_status: pass / reexam / fail / incomplete / not_graded
     */
    public function index(Section $section)
    {
        abort_unless(Auth::user()->teacher->id === $section->teacher_id, 403);

        $enrollments = $section->enrollments()
            ->with('student.user', 'grades.component')
            ->where('status', 'enrolled')
            ->orderBy('grade_status')
            ->get();

        // Group by status for the report cards
        $grouped = $enrollments->groupBy('grade_status');

        $summary = [
            'total'       => $enrollments->count(),
            'pass'        => $grouped->get('pass', collect())->count(),
            'reexam'      => $grouped->get('reexam', collect())->count(),
            'fail'        => $grouped->get('fail', collect())->count(),
            'incomplete'  => $grouped->get('incomplete', collect())->count(),
            'not_graded'  => $grouped->get('not_graded', collect())->count(),
        ];

        // Grade distribution: how many A, B, C, D, F
        $gradeDistribution = $enrollments
            ->whereNotNull('letter_grade')
            ->groupBy(fn($e) => $this->gradeGroup($e->letter_grade))
            ->map->count();

        return view('teacher.reports.index', compact(
            'section', 'enrollments', 'grouped', 'summary', 'gradeDistribution'
        ));
    }

    /**
     * Download report as PDF
     */
    public function pdf(Section $section)
    {
        abort_unless(Auth::user()->teacher->id === $section->teacher_id, 403);

        $enrollments = $section->enrollments()
            ->with('student.user', 'grades.component')
            ->where('status', 'enrolled')
            ->orderBy('grade_status')
            ->get();

        $grouped = $enrollments->groupBy('grade_status');
        $summary = [
            'total'      => $enrollments->count(),
            'pass'       => $grouped->get('pass', collect())->count(),
            'reexam'     => $grouped->get('reexam', collect())->count(),
            'fail'       => $grouped->get('fail', collect())->count(),
            'incomplete' => $grouped->get('incomplete', collect())->count(),
        ];

        $pdf = Pdf::loadView('teacher.reports.pdf', compact('section', 'enrollments', 'grouped', 'summary'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download("grade_report_{$section->course->code}_{$section->name}.pdf");
    }

    /**
     * Download report as Excel
     */
    public function excel(Section $section)
    {
        abort_unless(Auth::user()->teacher->id === $section->teacher_id, 403);

        return Excel::download(
            new GradeReportExport($section),
            "grade_report_{$section->course->code}_{$section->name}.xlsx"
        );
    }

    private function gradeGroup(string $letter): string
    {
        return match(true) {
            in_array($letter, ['A+', 'A', 'A-']) => 'A',
            in_array($letter, ['B+', 'B', 'B-']) => 'B',
            in_array($letter, ['C+', 'C', 'C-']) => 'C',
            $letter === 'D'                       => 'D',
            default                               => 'F',
        };
    }
}