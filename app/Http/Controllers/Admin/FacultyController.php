<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
 
class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculty::with([
            'departments.programs',
            'departments.teachers',
        ])->withCount('departments')->get();
 
        return view('admin.faculties.index', compact('faculties'));
    }
 
    public function create()
    {
        return view('admin.faculties.create');
    }
 
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:20|unique:faculties',
            'dean_name' => 'nullable|string|max:255',
        ]);
 
        Faculty::create($data);
        return redirect()->route('admin.faculties.index')->with('success', 'Faculty created.');
    }
 
    public function edit(Faculty $faculty)
    {
        return view('admin.faculties.edit', compact('faculty'));
    }
 
    public function update(Request $request, Faculty $faculty)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => 'required|string|max:20|unique:faculties,code,' . $faculty->id,
            'dean_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
 
        $faculty->update($data);
        return back()->with('success', 'Faculty updated.');
    }
}