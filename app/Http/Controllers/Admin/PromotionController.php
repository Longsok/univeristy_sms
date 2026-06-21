<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, Program, ClassGroup, PromotionHistory};
use App\Services\GpaCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class PromotionController extends Controller
{
    public function __construct(private GpaCalculatorService $gpa) {}

    public function index(Request $request)
    {
        $programs     = Program::with('department.faculty')->where('is_active', true)->get();
        $selectedYear = $request->year_level ?? 1;
        $selectedProg = $request->program_id ?? null;

        $query = ClassGroup::with([
            'program.department.faculty',
            'students.user',
            'students.enrollments' => fn($q) => $q->where('grades_finalised', true),
        ])
        ->where('year_level', $selectedYear)
        ->where('is_active', true);

        if ($selectedProg) $query->where('program_id', $selectedProg);

        $classGroups = $query->orderBy('program_id')->orderBy('name')->get();
        $groupStats  = $classGroups->map(fn($g) => $this->buildGroupStats($g));

        $ungroupedQuery = Student::with([
            'user', 'program',
            'enrollments' => fn($q) => $q->where('grades_finalised', true),
        ])->whereNull('class_group_id')->where('year_level', $selectedYear)->where('status', 'active');

        if ($selectedProg) $ungroupedQuery->where('program_id', $selectedProg);

        $ungrouped = $ungroupedQuery->get()->map(fn($s) => $this->buildStudentStats($s));

        $recentHistory = PromotionHistory::with('student.user', 'classGroup', 'promotedBy')
            ->latest()->take(8)->get();

        return view('admin.promotion.index', compact(
            'programs', 'groupStats', 'ungrouped',
            'selectedYear', 'selectedProg', 'recentHistory'
        ));
    }

    public function promote(Request $request)
    {
        $data = $request->validate([
            'student_ids'    => 'required|array|min:1',
            'student_ids.*'  => 'exists:students,id',
            'class_group_id' => 'nullable|exists:class_groups,id',
            'academic_year'  => 'nullable|string|max:20',
            'notes'          => 'nullable|string|max:500',
        ], ['student_ids.required' => 'Please select at least one student.']);

        DB::transaction(function () use ($data) {
            $academicYear = $data['academic_year'] ?? date('Y').'-'.(date('Y')+1);
            $adminId      = Auth::id();

            $students = Student::with([
                'enrollments' => fn($q) => $q->where('grades_finalised', true),
            ])->whereIn('id', $data['student_ids'])->where('status', 'active')->get();

            foreach ($students as $student) {
                $fromYear = $student->year_level;
                $gpaData  = $this->gpa->calculate($student);

                $student->increment('year_level');

                PromotionHistory::create([
                    'student_id'       => $student->id,
                    'class_group_id'   => $student->class_group_id,
                    'promoted_by'      => $adminId,
                    'from_year'        => $fromYear,
                    'to_year'          => $fromYear + 1,
                    'type'             => 'promotion',
                    'academic_year'    => $academicYear,
                    'gpa_at_promotion' => $gpaData['gpa'],
                    'notes'            => $data['notes'] ?? null,
                ]);
            }

            // If ALL active students in the group are promoted → update group year_level too
            if (!empty($data['class_group_id'])) {
                $group      = ClassGroup::find($data['class_group_id']);
                $allInGroup = $group?->students()->where('status', 'active')->pluck('id')->toArray() ?? [];
                $allPromoted = empty(array_diff($allInGroup, $data['student_ids']));
                if ($group && $allPromoted) {
                    $group->increment('year_level');
                }
            }
        });

        return back()->with('success', count($data['student_ids']).' students promoted.');
    }

    public function graduate(Request $request)
    {
        $data = $request->validate([
            'student_ids'    => 'required|array|min:1',
            'student_ids.*'  => 'exists:students,id',
            'class_group_id' => 'nullable|exists:class_groups,id',
            'academic_year'  => 'nullable|string|max:20',
        ]);

        DB::transaction(function () use ($data) {
            $academicYear = $data['academic_year'] ?? date('Y').'-'.(date('Y')+1);

            $students = Student::with([
                'enrollments' => fn($q) => $q->where('grades_finalised', true),
            ])->whereIn('id', $data['student_ids'])->get();

            foreach ($students as $student) {
                $gpaData = $this->gpa->calculate($student);
                $student->update(['status' => 'graduated']);
                PromotionHistory::create([
                    'student_id'       => $student->id,
                    'class_group_id'   => $student->class_group_id,
                    'promoted_by'      => Auth::id(),
                    'from_year'        => $student->year_level,
                    'to_year'          => $student->year_level,
                    'type'             => 'graduation',
                    'academic_year'    => $academicYear,
                    'gpa_at_promotion' => $gpaData['gpa'],
                ]);
            }

            if (!empty($data['class_group_id'])) {
                $group      = ClassGroup::find($data['class_group_id']);
                $activeLeft = $group?->students()->where('status', 'active')->count() ?? 0;
                if ($group && $activeLeft === 0) $group->update(['is_active' => false]);
            }
        });

        return back()->with('success', count($data['student_ids']).' students graduated.');
    }

    public function history(Request $request)
    {
        $history = PromotionHistory::with('student.user', 'classGroup', 'promotedBy')
            ->when($request->type,          fn($q) => $q->where('type', $request->type))
            ->when($request->academic_year, fn($q) => $q->where('academic_year', $request->academic_year))
            ->latest()->paginate(20);

        $academicYears = PromotionHistory::distinct()->pluck('academic_year')->filter()->sort()->reverse();

        return view('admin.promotion.history', compact('history', 'academicYears'));
    }

    private function buildGroupStats(ClassGroup $group): array
    {
        $students = $group->students->map(fn($s) => $this->buildStudentStats($s));
        return [
            'group'       => $group,
            'students'    => $students,
            'total'       => $students->count(),
            'eligible'    => $students->where('eligible', true)->count(),
            'notEligible' => $students->where('eligible', false)->count(),
            'avgGpa'      => $students->count() > 0 ? round($students->avg('gpa'), 2) : 0,
        ];
    }

    private function buildStudentStats(Student $student): array
    {
        $gpa    = $this->gpa->calculate($student);
        $fails  = $student->enrollments->where('grade_status', 'fail')->count();
        $reexam = $student->enrollments->where('grade_status', 'reexam')->count();

        // Count how many DISTINCT semesters have been finalised
        // for this student at their current year level
        $finalisedSemesters = $student->enrollments
            ->where('grades_finalised', true)
            ->filter(fn($e) => $e->section?->course?->year_level == $student->year_level)
            ->map(fn($e) => $e->section?->course?->semester_id)
            ->filter()
            ->unique()
            ->count();

        $semestersComplete = $finalisedSemesters >= 2;

        $eligible = $gpa['gpa'] >= 1.0
                && $fails === 0
                && $reexam === 0
                && $semestersComplete;

        return [
            'student'            => $student,
            'gpa'                => $gpa['gpa'],
            'credits'            => $gpa['total_credits'],
            'fails'              => $fails,
            'reexam'             => $reexam,
            'finalised_semesters'=> $finalisedSemesters,
            'semesters_complete' => $semestersComplete,
            'eligible'           => $eligible,
        ];
    }
}