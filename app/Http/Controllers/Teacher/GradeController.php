<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Section, Grade, GradeComponent, Enrollment, AttendanceRecord};
use App\Services\{GradeStatusService, GradeImportService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function __construct(
        private GradeStatusService $gradeStatus,
        private GradeImportService $gradeImporter,
    ) {}

    public function index(Section $section)
    {
        $this->authorizeTeacher($section);

        $components  = $section->gradeComponents;
        $enrollments = $section->enrollments()
            ->with('student.user', 'grades.component')
            ->where('status', 'enrolled')
            ->get();

        // ── Auto-fill attendance score from attendance records ────────────────
        $attendanceComponent = $components->first(
            fn($c) => str_contains(strtolower($c->name), 'attendance')
        );

        if ($attendanceComponent) {
            $changed = false;
            foreach ($enrollments as $enrollment) {
                // Only auto-fill if teacher hasn't manually entered it yet
                $existingGrade = $enrollment->grades
                    ->firstWhere('grade_component_id', $attendanceComponent->id);

                if (!$existingGrade) {
                    $records = AttendanceRecord::where('section_id', $section->id)
                        ->where('student_id', $enrollment->student_id)
                        ->get();

                    if ($records->count() > 0) {
                        $score = AttendanceRecord::calculateScore($records->all());
                        Grade::create([
                            'enrollment_id'      => $enrollment->id,
                            'grade_component_id' => $attendanceComponent->id,
                            'score'              => $score,
                        ]);
                        $changed = true;
                    }
                }
            }

            // Reload grades if we auto-filled anything
            if ($changed) {
                $enrollments->load('grades.component');
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        return view('teacher.grades.index', compact('section', 'components', 'enrollments'));
    }

    public function upsert(Request $request, Section $section)
    {
        $this->authorizeTeacher($section);

        $data = $request->validate([
            'grades'                      => 'required|array',
            'grades.*.enrollment_id'      => 'required|exists:enrollments,id',
            'grades.*.grade_component_id' => 'required|exists:grade_components,id',
            'grades.*.score'              => 'required|numeric|min:0',
            'grades.*.remarks'            => 'nullable|string|max:255',
        ]);

        foreach ($data['grades'] as $entry) {
            // Get the component to clamp score to weight_percent max
            $component = GradeComponent::find($entry['grade_component_id']);
            $maxScore  = $component ? $component->weight_percent : 100;

            Grade::updateOrCreate(
                [
                    'enrollment_id'      => $entry['enrollment_id'],
                    'grade_component_id' => $entry['grade_component_id'],
                ],
                [
                    'score'   => min((float) $entry['score'], (float) $maxScore),
                    'remarks' => $entry['remarks'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Grades saved.');
    }

    public function syncAttendance(Section $section)
    {
        $this->authorizeTeacher($section);

        $attendanceComponent = $section->gradeComponents->first(
            fn($c) => str_contains(strtolower($c->name), 'attendance')
        );

        if (!$attendanceComponent) {
            return back()->with('error', 'No Attendance grade component found. Add one in the section settings first.');
        }

        $enrollments = $section->enrollments()
            ->where('status', 'enrolled')
            ->get();

        $count = 0;
        foreach ($enrollments as $enrollment) {
            $records = AttendanceRecord::where('section_id', $section->id)
                ->where('student_id', $enrollment->student_id)
                ->get();

            $score = $records->count() > 0
                ? AttendanceRecord::calculateScore($records->all())
                : 0;

            // Always overwrite — sync means fresh calculation
            Grade::updateOrCreate(
                [
                    'enrollment_id'      => $enrollment->id,
                    'grade_component_id' => $attendanceComponent->id,
                ],
                ['score' => $score]
            );

            $count++;
        }

        return back()->with('success', "Attendance scores synced for {$count} students.");
    }

    public function enterReexamScore(Request $request, Section $section)
    {
        $this->authorizeTeacher($section);

        $data = $request->validate([
            'reexam_scores'                => 'required|array',
            'reexam_scores.*.grade_id'     => 'required|exists:grades,id',
            'reexam_scores.*.reexam_score' => 'required|numeric|min:0',
        ]);

        foreach ($data['reexam_scores'] as $entry) {
            Grade::where('id', $entry['grade_id'])->update([
                'reexam_score' => $entry['reexam_score'],
            ]);
        }

        return back()->with('success', 'Re-exam scores saved.');
    }

    public function finalise(Section $section)
    {
        $this->authorizeTeacher($section);

        abort_unless($section->isGradingConfigured(), 422, 'Grade components must sum to 100%.');

        $enrollments = $section->enrollments()->where('status', 'enrolled')->get();

        foreach ($enrollments as $enrollment) {
            $this->gradeStatus->finalise($enrollment);
        }

        return back()->with('success', 'Grades finalised. Student statuses updated.');
    }

    public function import(Request $request, Section $section)
    {
        $this->authorizeTeacher($section);

        $request->validate(['file' => 'required|file|mimes:xlsx,csv|max:5120']);

        $result = $this->gradeImporter->handle($request->file('file'), $section);

        return back()->with('success', "Imported {$result['imported']} grade entries.");
    }

    private function authorizeTeacher(Section $section): void
    {
        abort_unless(Auth::user()->teacher->id === $section->teacher_id, 403);
    }
}