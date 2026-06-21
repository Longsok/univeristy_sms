<?php
// app/Http/Controllers/Admin/ClassGroupController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ClassGroup, Program, Section, Enrollment, Student};
use Illuminate\Http\Request;

class ClassGroupController extends Controller
{
    public function index()
    {
        $classGroups = ClassGroup::with('program.department.faculty')
            ->withCount('students')
            ->orderBy('program_id')
            ->orderBy('year_level')
            ->orderBy('name')
            ->get();

        $programs = Program::with('department.faculty')
            ->where('is_active', true)
            ->get();

        return view('admin.class-groups.index', compact('classGroups', 'programs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'program_id'  => 'required|exists:programs,id',
            'name'        => 'required|string|max:20',
            'description' => 'nullable|string|max:255',
            'year_level'  => 'required|integer|min:1|max:6',
            'capacity'    => 'required|integer|min:1|max:200',
        ]);

        ClassGroup::create($data);

        return back()->with('success', "Class group '{$data['name']}' created.");
    }

    public function update(Request $request, ClassGroup $classGroup)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:20',
            'description' => 'nullable|string|max:255',
            'year_level'  => 'required|integer|min:1|max:6',
            'capacity'    => 'required|integer|min:1|max:200',
            'is_active'   => 'boolean',
        ]);

        $classGroup->update($data);

        return back()->with('success', 'Class group updated.');
    }

    public function destroy(ClassGroup $classGroup)
    {
        // Remove from students first
        Student::where('class_group_id', $classGroup->id)
            ->update(['class_group_id' => null]);

        $classGroup->delete();

        return back()->with('success', 'Class group deleted.');
    }

    /**
     * Show students in a class group + manage membership
     */
    public function show(ClassGroup $classGroup)
    {
        $classGroup->load('program.department.faculty', 'students.user');

        // Students not in any group in this program/year
        $available = Student::with('user', 'program')
            ->where('program_id', $classGroup->program_id)
            ->where('year_level', $classGroup->year_level)
            ->whereNull('class_group_id')
            ->get();

        return view('admin.class-groups.show', compact('classGroup', 'available'));
    }

    /**
     * Add students to a class group
     */
    public function addStudents(Request $request, ClassGroup $classGroup)
    {
        $data = $request->validate([
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        Student::whereIn('id', $data['student_ids'])
            ->update(['class_group_id' => $classGroup->id]);

        return back()->with('success', count($data['student_ids']) . ' students added to ' . $classGroup->name);
    }

    /**
     * Remove a student from their class group
     */
    public function removeStudent(ClassGroup $classGroup, Student $student)
    {
        $student->update(['class_group_id' => null]);
        return back()->with('success', $student->user->name . ' removed from ' . $classGroup->name);
    }

    /**
     * Bulk enroll an entire class group into a section
     */
    public function enrollToSection(Request $request, ClassGroup $classGroup)
    {
        $data = $request->validate([
            'section_id' => 'required|exists:sections,id',
        ]);

        $section  = Section::findOrFail($data['section_id']);
        $students = $classGroup->students;
        $count    = 0;

        foreach ($students as $student) {
            $result = Enrollment::firstOrCreate(
                ['student_id' => $student->id, 'section_id' => $section->id],
                ['status' => 'enrolled']
            );
            if ($result->wasRecentlyCreated) $count++;
        }

        return back()->with('success',
            "{$count} students from {$classGroup->name} enrolled into {$section->course->code} — {$section->name}."
        );
    }
}