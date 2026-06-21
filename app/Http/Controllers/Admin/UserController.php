<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Student, Teacher, Program, Department, Faculty};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ── Flat user list (all roles) ────────────────────────────────────────────
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->role,   fn($q) => $q->where('role', $request->role))
            ->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->search}%")
                   ->orWhere('name_kh', 'like', "%{$request->search}%")
                   ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->with(['student.program.department.faculty', 'student.classGroup',
                    'teacher.department.faculty'])
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    // ── Students: Faculty → Department → Program cards ────────────────────────
    public function students()
    {
        $faculties = Faculty::with([
            'departments.programs' => fn($q) => $q->withCount([
                'students' => fn($sq) => $sq->where('status', 'active')
            ])->where('is_active', true),
            'departments.programs.students' => fn($q) => $q->where('status', 'active'),
        ])
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

        // Total counts
        $totalStudents  = Student::where('status', 'active')->count();
        $totalPrograms  = Program::where('is_active', true)->count();
        $totalFaculties = $faculties->count();

        return view('admin.users.students', compact(
            'faculties', 'totalStudents', 'totalPrograms', 'totalFaculties'
        ));
    }

    // ── Students by program: grouped by batch ─────────────────────────────────
    public function studentsByProgram(Request $request, Program $program)
    {
        $program->load('department.faculty');

        $query = Student::with(['user', 'classGroup'])
            ->where('program_id', $program->id)
            ->when($request->batch,      fn($q) => $q->where('batch', $request->batch))
            ->when($request->year_level, fn($q) => $q->where('year_level', $request->year_level))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->search,     fn($q) => $q->whereHas('user', fn($u) =>
                $u->where('name', 'like', "%{$request->search}%")
                  ->orWhere('student_id', 'like', "%{$request->search}%")
            ));

        $students  = $query->orderBy('year_level')->orderBy('student_id')->get();
        $byBatch   = $students->groupBy(fn($s) => $s->batch ?? 0)->sortKeys();

        $batches = Student::where('program_id', $program->id)
            ->distinct()->orderBy('batch')->pluck('batch')->filter();
        $years   = Student::where('program_id', $program->id)
            ->distinct()->orderBy('year_level')->pluck('year_level');

        return view('admin.users.students_program', compact(
            'program', 'students', 'byBatch', 'batches', 'years'
        ));
    }

    // ── Teachers: Faculty → Department → Teacher list ─────────────────────────
    public function teachers()
    {
        $faculties = Faculty::with([
            'departments.teachers.user',
            'departments.teachers.sections.course',
        ])
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

        $totalTeachers  = Teacher::count();
        $totalDepts     = Department::where('is_active', true)->count();

        return view('admin.users.teachers', compact(
            'faculties', 'totalTeachers', 'totalDepts'
        ));
    }

    // ── Teachers by department ────────────────────────────────────────────────
    public function teachersByDepartment(Request $request, Department $department)
    {
        $department->load('faculty');

        $teachers = Teacher::with([
            'user',
            'sections' => fn($q) => $q->with('course.semester', 'enrollments'),
        ])
        ->where('department_id', $department->id)
        ->when($request->search, fn($q) => $q->whereHas('user', fn($u) =>
            $u->where('name', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%")
        ))
        ->get();

        return view('admin.users.teachers_department', compact('department', 'teachers'));
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────
    public function create()
    {
        $programs    = Program::with('department.faculty')->where('is_active', true)->get();
        $departments = Department::with('faculty')->where('is_active', true)->get();
        return view('admin.users.create', compact('programs', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'name_kh'        => 'nullable|string|max:255',
            'email'          => 'required|email|unique:users',
            'password'       => 'required|min:8|confirmed',
            'role'           => 'required|in:teacher,student',
            'phone'          => 'nullable|string|max:20',
            'student_id'     => 'required_if:role,student|nullable|string|unique:students,student_id',
            'program_id'     => 'required_if:role,student|nullable|exists:programs,id',
            'year_level'     => 'required_if:role,student|nullable|integer|min:1|max:6',
            'batch'          => 'nullable|integer|min:1|max:999',
            'date_of_birth'  => 'nullable|date',
            'employee_id'    => 'required_if:role,teacher|nullable|string|unique:teachers,employee_id',
            'department_id'  => 'required_if:role,teacher|nullable|exists:departments,id',
            'specialization' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'name_kh'  => $data['name_kh'] ?? null,
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'phone'    => $data['phone'] ?? null,
        ]);

        if ($data['role'] === 'student') {
            Student::create([
                'user_id'       => $user->id,
                'program_id'    => $data['program_id'],
                'student_id'    => $data['student_id'],
                'year_level'    => $data['year_level'],
                'batch'         => $data['batch'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
            ]);
        } else {
            Teacher::create([
                'user_id'        => $user->id,
                'department_id'  => $data['department_id'],
                'employee_id'    => $data['employee_id'],
                'specialization' => $data['specialization'] ?? null,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $programs    = Program::with('department.faculty')->where('is_active', true)->get();
        $departments = Department::with('faculty')->where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'programs', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'name_kh'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);
        $user->update($data);
        return back()->with('success', 'User updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ], ['password.confirmed' => 'Passwords do not match.']);
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', "Password reset for {$user->name}.");
    }

    public function updateScholarship(Request $request, \App\Models\Student $student)
    {
        $request->validate([
            'scholarship_type' => 'required|in:paid,partial,full',
        ]);
    
        $student->update(['scholarship_type' => $request->scholarship_type]);
    
        $labels = [
            'paid'    => 'Self-Funded',
            'partial' => 'Partial Scholarship',
            'full'    => 'Full Scholarship',
        ];
    
        return back()->with('success',
            "{$student->user->name} updated to {$labels[$request->scholarship_type]}."
        );
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'User ' . ($user->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} permanently deleted.");
    }
}