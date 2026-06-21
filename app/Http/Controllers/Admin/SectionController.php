<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Section, Course, Teacher, ClassGroup, Enrollment};
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $sections = Section::with([
            'course.department',
            'course.program',
            'teacher.user',
            'enrollments',
        ])
        ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
        ->get();

        $courses = Course::with('department')->orderBy('code')->get();

        return view('admin.sections.index', compact('sections', 'courses'));
    }

    public function create()
    {
        $courses     = Course::with('department.faculty', 'program', 'semester')
                           ->orderBy('code')->get();
        $teachers    = Teacher::with('user', 'department')->get();
        $classGroups = ClassGroup::with('program')
                           ->withCount('students')
                           ->where('is_active', true)
                           ->orderBy('program_id')
                           ->orderBy('year_level')
                           ->orderBy('name')
                           ->get();

        return view('admin.sections.create', compact('courses', 'teachers', 'classGroups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id'      => 'required|exists:courses,id',
            'teacher_id'     => 'nullable|exists:teachers,id',
            'name'           => 'required|string|max:50',
            'max_students'   => 'required|integer|min:1|max:200',
            'class_group_id' => 'nullable|exists:class_groups,id',
        ]);

        $classGroupId = $data['class_group_id'] ?? null;
        unset($data['class_group_id']);

        $section = Section::create($data);

        // Auto-enroll class group students if selected
        if ($classGroupId) {
            $classGroup = ClassGroup::with('students')->find($classGroupId);
            $count = 0;
            foreach ($classGroup->students as $student) {
                Enrollment::firstOrCreate(
                    ['student_id' => $student->id, 'section_id' => $section->id],
                    ['status' => 'enrolled']
                );
                $count++;
            }
            return redirect()->route('admin.sections.index')
                ->with('success', "Section created and {$count} students from {$classGroup->name} enrolled automatically.");
        }

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created.');
    }

    public function edit(Section $section)
    {
        $courses  = Course::with('department.faculty', 'program', 'semester')
                        ->orderBy('code')->get();
        $teachers = Teacher::with('user', 'department')->get();
        $section->load('gradeComponents');

        return view('admin.sections.edit', compact('section', 'courses', 'teachers'));
    }

    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'course_id'    => 'required|exists:courses,id',
            'teacher_id'   => 'nullable|exists:teachers,id',
            'name'         => 'required|string|max:50',
            'max_students' => 'required|integer|min:1|max:200',
        ]);

        $section->update($data);
        return back()->with('success', 'Section updated.');
    }

    public function destroy(Section $section)
    {
        $section->delete();
        return back()->with('success', 'Section deleted.');
    }

    public function printStudents(Section $section)
    {
        $section->load('course.program.department', 'teacher.user');
 
        $enrollments = $section->enrollments()
            ->where('status', 'enrolled')
            ->with('student.user', 'student.program')
            ->orderBy('created_at')
            ->get();
 
        return view('admin.sections.print-students', compact('section', 'enrollments'));
    }
}