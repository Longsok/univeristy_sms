<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Section, Enrollment, Grade, GradeComponent};
use App\Services\GradeStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReexamController extends Controller
{
    public function __construct(private GradeStatusService $gradeStatus) {}

    /**
     * Show all students eligible for re-exam in a section
     */
    public function index(Section $section)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->id === $section->teacher_id, 403);

        $section->load('course', 'gradeComponents');

        // Get enrollments with reexam status
        $reexamEnrollments = Enrollment::where('section_id', $section->id)
            ->where('grade_status', 'reexam')
            ->with('student.user', 'grades.component')
            ->get();

        return view('teacher.reexam.index', compact('section', 'reexamEnrollments'));
    }

    /**
     * Save re-exam scores and recalculate final grade
     */
    public function save(Request $request, Section $section)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->id === $section->teacher_id, 403);

        $data = $request->validate([
            'reexam'                         => 'required|array',
            'reexam.*.enrollment_id'         => 'required|exists:enrollments,id',
            'reexam.*.grade_component_id'    => 'required|exists:grade_components,id',
            'reexam.*.reexam_score'          => 'required|numeric|min:0',
        ]);

        foreach ($data['reexam'] as $entry) {
            $component = GradeComponent::find($entry['grade_component_id']);
            $maxScore  = $component ? $component->weight_percent : 100;

            Grade::updateOrCreate(
                [
                    'enrollment_id'      => $entry['enrollment_id'],
                    'grade_component_id' => $entry['grade_component_id'],
                ],
                [
                    'reexam_score' => min((float) $entry['reexam_score'], (float) $maxScore),
                ]
            );
        }

        // Recalculate final grades for all affected enrollments
        $enrollmentIds = collect($data['reexam'])->pluck('enrollment_id')->unique();
        foreach ($enrollmentIds as $enrollmentId) {
            $enrollment = Enrollment::with('grades', 'section.gradeComponents')->find($enrollmentId);
            if ($enrollment) {
                $this->gradeStatus->finalise($enrollment);
            }
        }

        return back()->with('success', 'Re-exam scores saved and grades recalculated.');
    }
}