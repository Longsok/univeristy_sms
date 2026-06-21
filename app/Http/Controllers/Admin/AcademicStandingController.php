<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, Department, Program};
use App\Services\GpaCalculatorService;
use Illuminate\Http\Request;

class AcademicStandingController extends Controller
{
    public function __construct(private GpaCalculatorService $gpa) {}

    /**
     * Overview — program cards grouped by faculty
     */
    public function index()
    {
        $programs = Program::with('department.faculty')
            ->withCount(['students' => fn($q) => $q->where('status', 'active')])
            ->where('is_active', true)
            ->orderBy('department_id')
            ->orderBy('name')
            ->get();

        return view('admin.standing.index', compact('programs'));
    }

    /**
     * Program detail — students grouped by batch
     */
    public function program(Request $request, Program $program)
    {
        $program->load('department.faculty');

        $query = Student::with([
            'user', 'program', 'classGroup',
            'enrollments' => fn($q) => $q->where('grades_finalised', true),
        ])
        ->where('program_id', $program->id)
        ->where('status', 'active');

        if ($request->batch)      $query->where('batch', $request->batch);
        if ($request->year_level) $query->where('year_level', $request->year_level);

        $students = $query->get()->map(function ($student) {
            $gpa = $this->gpa->calculate($student);

            $standing = match(true) {
                $gpa['gpa'] >= 3.5 => ['label' => "Dean's List",        'color' => '#166534', 'bg' => '#dcfce7', 'icon' => 'bi-trophy-fill'],
                $gpa['gpa'] >= 2.0 => ['label' => 'Good Standing',      'color' => '#1e40af', 'bg' => '#dbeafe', 'icon' => 'bi-check-circle-fill'],
                $gpa['gpa'] >= 1.5 => ['label' => 'Academic Warning',   'color' => '#c2410c', 'bg' => '#ffedd5', 'icon' => 'bi-exclamation-triangle-fill'],
                $gpa['gpa'] >= 1.0 => ['label' => 'Academic Probation', 'color' => '#991b1b', 'bg' => '#fee2e2', 'icon' => 'bi-x-circle-fill'],
                default            => ['label' => 'Critical',           'color' => '#7f1d1d', 'bg' => '#fef2f2', 'icon' => 'bi-x-octagon-fill'],
            };

            return [
                'student'  => $student,
                'gpa'      => $gpa['gpa'],
                'credits'  => $gpa['total_credits'],
                'standing' => $standing,
            ];
        })->sortByDesc('gpa');

        // Group by batch then year
        $byBatch = $students->groupBy(fn($r) => $r['student']->batch ?? 0)
            ->sortKeys();

        // Available batches and years for filter
        $batches = Student::where('program_id', $program->id)
            ->where('status', 'active')
            ->distinct()->orderBy('batch')->pluck('batch')->filter();

        $years = Student::where('program_id', $program->id)
            ->where('status', 'active')
            ->distinct()->orderBy('year_level')->pluck('year_level');

        $summary = [
            "Dean's List"        => $students->where('standing.label', "Dean's List")->count(),
            'Good Standing'      => $students->where('standing.label', 'Good Standing')->count(),
            'Academic Warning'   => $students->where('standing.label', 'Academic Warning')->count(),
            'Academic Probation' => $students->where('standing.label', 'Academic Probation')->count(),
            'Critical'           => $students->where('standing.label', 'Critical')->count(),
        ];

        return view('admin.standing.program', compact(
            'program', 'byBatch', 'summary', 'batches', 'years'
        ));
    }
}