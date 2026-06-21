<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Course, Department, Program, Semester};
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $programs = Program::with([
            'department.faculty',
            'courses' => fn($q) => $q->withCount('sections'),
        ])
        ->withCount('courses')
        ->where('is_active', true)
        ->orderBy('department_id')
        ->orderBy('name')
        ->get();

        $programsByFaculty = $programs->groupBy(fn($p) => $p->department->faculty->name);
        $totalCourses      = Course::count();

        return view('admin.courses.index', compact(
            'programs', 'programsByFaculty', 'totalCourses'
        ));
    }

    public function program(Request $request, Program $program)
    {
        $program->load('department.faculty');

        $courses = Course::with('semester')
            ->withCount('sections')
            ->where('program_id', $program->id)
            ->when($request->year, fn($q) => $q->where('year_level', $request->year))
            ->orderBy('year_level')
            ->orderBy('code')
            ->get();

        $coursesByYear = $courses->groupBy('year_level');

        $years = Course::where('program_id', $program->id)
            ->distinct()
            ->orderBy('year_level')
            ->pluck('year_level');

        return view('admin.courses.program', compact(
            'program', 'courses', 'coursesByYear', 'years'
        ));
    }

    public function create(Request $request)
    {
        $programs  = Program::with('department.faculty')->where('is_active', true)->get();
        $selectedProgram = $request->program_id;
        $selectedYear    = $request->year_level;

        // Load semesters filtered by year_level if provided
        // This ensures admin sees the correct semester for the year they're creating a course for
        $semesters = $this->getSemestersForYear($selectedYear);

        return view('admin.courses.create', compact(
            'programs', 'semesters', 'selectedProgram', 'selectedYear'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'program_id'    => 'required|exists:programs,id',
            'department_id' => 'required|exists:departments,id',
            'semester_id'   => 'required|exists:semesters,id',
            'year_level'    => 'required|integer|min:1|max:6',
            'code'          => 'required|string|max:20|unique:courses',
            'name'          => 'required|string|max:255',
            'credit_units'  => 'required|integer|min:1|max:6',
            'description'   => 'nullable|string',
        ]);

        $course = Course::create($data);

        return redirect()
            ->route('admin.courses.program', $course->program_id)
            ->with('success', "Course {$course->code} created.");
    }

    public function edit(Course $course)
    {
        $programs  = Program::with('department.faculty')->where('is_active', true)->get();
        $semesters = $this->getSemestersForYear($course->year_level);
        return view('admin.courses.edit', compact('course', 'programs', 'semesters'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'program_id'    => 'required|exists:programs,id',
            'department_id' => 'required|exists:departments,id',
            'semester_id'   => 'required|exists:semesters,id',
            'year_level'    => 'required|integer|min:1|max:6',
            'code'          => 'required|string|max:20|unique:courses,code,'.$course->id,
            'name'          => 'required|string|max:255',
            'credit_units'  => 'required|integer|min:1|max:6',
            'description'   => 'nullable|string',
        ]);

        $course->update($data);

        return redirect()
            ->route('admin.courses.program', $course->program_id)
            ->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $programId = $course->program_id;
        $course->delete();
        return redirect()
            ->route('admin.courses.program', $programId)
            ->with('success', 'Course deleted.');
    }

    public function getProgramsByDepartment(Request $request)
    {
        $programs = Program::where('department_id', $request->department_id)
            ->where('is_active', true)
            ->get(['id', 'name', 'code']);
        return response()->json($programs);
    }

    /**
     * Get semesters for a specific year level.
     * Returns year-specific semesters first, then general ones.
     * If year_level not provided, returns all active semesters.
     */
    private function getSemestersForYear(?int $yearLevel): \Illuminate\Database\Eloquent\Collection
    {
        $query = Semester::orderByDesc('academic_year')
            ->orderBy('semester_number');

        if ($yearLevel) {
            // Year-specific first, then general (null year_level)
            return $query->where(function($q) use ($yearLevel) {
                $q->where('year_level', $yearLevel)
                  ->orWhereNull('year_level');
            })->get();
        }

        return $query->get();
    }
}