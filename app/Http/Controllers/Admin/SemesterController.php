<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::withCount('courses')
            ->orderByDesc('academic_year')
            ->orderBy('year_level')
            ->orderBy('semester_number')
            ->get();

        $byYear     = $semesters->groupBy('academic_year');
        $activeYear = Semester::currentAcademicYear();

        return view('admin.semesters.index', compact('byYear', 'activeYear'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'academic_year'   => ['required','string','max:20','regex:/^\d{4}-\d{4}$/'],
            'semester_number' => 'required|integer|in:1,2',
            'year_level'      => 'nullable|integer|min:1|max:6',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after:start_date',
        ]);

        $data['year_level'] = $data['year_level'] ?: null;

        // Auto-activate if adding to current academic year
        $data['is_active'] = ($data['academic_year'] === Semester::currentAcademicYear());

        Semester::create($data);


        $yearLabel = $data['year_level'] ? "Year {$data['year_level']}" : 'All Years';
        return back()->with('success', "{$data['name']} ({$yearLabel}, {$data['academic_year']}) added.");
    }

    public function update(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'year_level' => 'nullable|integer|min:1|max:6',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $data['year_level'] = $data['year_level'] ?: null;
        $semester->update($data);
        return back()->with('success', 'Semester updated.');
    }

    public function destroy(Semester $semester)
    {
        if ($semester->courses()->count() > 0) {
            return back()->with('error',
                "Cannot delete — {$semester->courses_count} courses are assigned to this semester."
            );
        }
        $semester->delete();
        return back()->with('success', 'Semester deleted.');
    }

    /**
     * Set an entire academic year as current.
     * ALL semesters in that year activate simultaneously.
     * Status (Running/Upcoming) is then determined by dates automatically.
     */
    public function setActiveYear(Request $request)
    {
        $year = $request->validate([
            'academic_year' => ['required','string','regex:/^\d{4}-\d{4}$/'],
        ])['academic_year'];

        Semester::query()->update(['is_active' => false]);
        $count = Semester::where('academic_year', $year)->update(['is_active' => true]);


        return back()->with('success',
            "Academic Year {$year} is now current. {$count} semester record(s) activated."
        );
    }
}