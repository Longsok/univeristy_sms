<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\{Program, Department};
use Illuminate\Http\Request;
 
class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $programs = Program::with('department.faculty')
            ->withCount('students')
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->get();
 
        return view('admin.programs.index', compact('programs'));
    }
 
    public function create()
    {
        $departments = Department::with('faculty')
            ->where('is_active', true)
            ->get();
 
        return view('admin.programs.create', compact('departments'));
    }
 
    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:20|unique:programs',
            'degree_level'  => 'required|in:certificate,associate,bachelor,master,doctorate',
            'total_credits' => 'required|integer|min:30|max:300',
        ]);
 
        Program::create($data);
        return redirect()->route('admin.programs.index')->with('success', 'Program created.');
    }
 
    public function edit(Program $program)
    {
        $departments = Department::with('faculty')
            ->where('is_active', true)
            ->get();
 
        return view('admin.programs.edit', compact('program', 'departments'));
    }
 
    public function update(Request $request, Program $program)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|max:20|unique:programs,code,' . $program->id,
            'degree_level'  => 'required|in:certificate,associate,bachelor,master,doctorate',
            'total_credits' => 'required|integer|min:30|max:300',
            'is_active'     => 'boolean',
        ]);
 
        $program->update($data);
        return back()->with('success', 'Program updated.');
    }
 
    public function destroy(Program $program)
    {
        if ($program->students()->count() > 0) {
            return back()->with('error', 'Cannot delete program with enrolled students.');
        }
 
        $program->delete();
        return back()->with('success', 'Program deleted.');
    }
}