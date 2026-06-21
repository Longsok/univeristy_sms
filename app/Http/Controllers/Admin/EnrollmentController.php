<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Enrollment, Student, Section, Program};
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $sections = Section::with('course.department', 'teacher.user')->get();
        $programs = Program::with('department')->where('is_active', true)->get();

        // If a section is selected, load its enrolled students
        $selectedSection = null;
        $enrollments     = collect();

        if ($request->section_id) {
            $selectedSection = Section::with([
                'course.program.department.faculty',
                'teacher.user',
                'enrollments.student.user',
                'enrollments.student.program',
            ])->find($request->section_id);

            if ($selectedSection) {
                $enrollments = $selectedSection->enrollments()
                    ->with('student.user', 'student.program')
                    ->orderBy('created_at')
                    ->get();
            }
        }

        // All students not yet enrolled in selected section
        $enrolledStudentIds = $enrollments->pluck('student_id');
        $availableStudents  = Student::with('user', 'program')
            ->whereNotIn('id', $enrolledStudentIds)
            ->get();

        return view('admin.enrollments.index', compact(
            'sections',
            'programs',
            'selectedSection',
            'enrollments',
            'availableStudents'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        Enrollment::firstOrCreate(
            ['student_id' => $request->student_id, 'section_id' => $request->section_id],
            ['status' => 'enrolled']
        );

        return redirect()
            ->route('admin.enrollments.index', ['section_id' => $request->section_id])
            ->with('success', 'Student enrolled successfully.');
    }

    public function enrollClassGroup(Request $request)
    {
        $request->validate([
            'section_id'     => 'required|exists:sections,id',
            'class_group_id' => 'required|exists:class_groups,id',
        ]);

        $classGroup = \App\Models\ClassGroup::with('students')->find($request->class_group_id);
        $count = 0;

        foreach ($classGroup->students as $student) {
            $result = Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'section_id' => $request->section_id],
                ['status' => 'enrolled']
            );
            if ($result->wasRecentlyCreated) $count++;
        }

        return redirect()
            ->route('admin.enrollments.index', ['section_id' => $request->section_id])
            ->with('success', "{$count} students from {$classGroup->name} enrolled.");
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'section_id'    => 'required|exists:sections,id',
        ]);

        $count = 0;
        foreach ($request->student_ids as $studentId) {
            $result = Enrollment::firstOrCreate(
                ['student_id' => $studentId, 'section_id' => $request->section_id],
                ['status' => 'enrolled']
            );
            if ($result->wasRecentlyCreated) $count++;
        }

        return redirect()
            ->route('admin.enrollments.index', ['section_id' => $request->section_id])
            ->with('success', "{$count} students enrolled.");
    }

    public function retake(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'section_id' => 'required|exists:sections,id',
        ]);
 
        // Check student previously failed this course
        $section    = \App\Models\Section::with('course')->find($data['section_id']);
        $previousFail = \App\Models\Enrollment::where('student_id', $data['student_id'])
            ->whereHas('section', fn($q) => $q->where('course_id', $section->course_id))
            ->where('grade_status', 'fail')
            ->exists();
 
        if (!$previousFail) {
            return back()->with('error', 'Student has no failed grade in this course.');
        }
 
        // Check not already enrolled in new section
        $alreadyEnrolled = \App\Models\Enrollment::where('student_id', $data['student_id'])
            ->where('section_id', $data['section_id'])
            ->exists();
 
        if ($alreadyEnrolled) {
            return back()->with('error', 'Student is already enrolled in this section.');
        }
 
        \App\Models\Enrollment::create([
            'student_id' => $data['student_id'],
            'section_id' => $data['section_id'],
            'status'     => 'enrolled',
        ]);
 
        return back()->with('success', 'Student enrolled for course retake.');
    }

    public function destroy(Enrollment $enrollment)
    {
        $sectionId = $enrollment->section_id;
        $enrollment->delete();

        return redirect()
            ->route('admin.enrollments.index', ['section_id' => $sectionId])
            ->with('success', 'Student removed from section.');
    }
}