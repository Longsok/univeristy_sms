<?php
// ============================================================
// app/Http/Controllers/Admin/DepartmentController.php
// ============================================================
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\{Department, Faculty};
use Illuminate\Http\Request;
 
class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::with(['faculty', 'programs', 'teachers'])
            ->withCount(['teachers', 'programs'])
            ->when($request->faculty_id, fn($q) => $q->where('faculty_id', $request->faculty_id))
            ->get();
 
        return view('admin.departments.index', compact('departments'));
    }
 
    public function create()
    {
        $faculties = Faculty::where('is_active', true)->get();
        return view('admin.departments.create', compact('faculties'));
    }
 
    public function store(Request $request)
    {
        $data = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:20|unique:departments',
            'head_name'  => 'nullable|string|max:255',
        ]);
 
        Department::create($data);
        return redirect()->route('admin.departments.index')->with('success', 'Department created.');
    }
 
    public function edit(Department $department)
    {
        $faculties = Faculty::where('is_active', true)->get();
        return view('admin.departments.edit', compact('department', 'faculties'));
    }
 
    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:20|unique:departments,code,' . $department->id,
            'head_name'  => 'nullable|string|max:255',
            'is_active'  => 'boolean',
        ]);
 
        $department->update($data);
        return back()->with('success', 'Department updated.');
    }
}