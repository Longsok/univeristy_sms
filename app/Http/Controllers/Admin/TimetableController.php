<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Timetable, Section, Program};
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    protected array $days = [
        'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'
    ];

    public function overview()
    {
        $programs = Program::with([
            'department.faculty',
            'courses.sections.timetables',
            'courses.sections.teacher.user',
            'courses.sections.enrollments',
        ])
        ->where('is_active', true)
        ->orderBy('department_id')
        ->orderBy('name')
        ->get()
        ->each(function ($program) {
            $program->all_sections = $program->courses->flatMap(fn($c) => $c->sections);
        });

        $days             = $this->days;
        $allSections      = $programs->flatMap(fn($p) => $p->all_sections);
        $withTimetable    = $allSections->filter(fn($s) => $s->timetables->count() > 0)->count();
        $withoutTimetable = $allSections->filter(fn($s) => $s->timetables->count() === 0)->count();

        return view('admin.timetable.overview', compact(
            'programs', 'days', 'withTimetable', 'withoutTimetable'
        ));
    }

    public function program(Program $program)
    {
        $program->load([
            'department.faculty',
            'courses.sections.timetables',
            'courses.sections.teacher.user',
            'courses.sections.enrollments',
        ]);

        $program->all_sections = $program->courses->flatMap(fn($c) => $c->sections);
        $days = $this->days;

        return view('admin.timetable.program', compact('program', 'days'));
    }

    public function index(Section $section)
    {
        $section->load([
            'course.program.department',
            'teacher.user',
            'timetables',
            'enrollments',
        ]);

        $days = $this->days;

        return view('admin.timetable.index', compact('section', 'days'));
    }

    public function store(Request $request, Section $section)
    {
        $data = $request->validate([
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'room'        => 'nullable|string|max:50',
        ]);

        $existing = $section->timetables()->where('day_of_week', $data['day_of_week'])->first();
        if ($existing) {
            return back()->withErrors([
                'day_of_week' => "{$data['day_of_week']} already has a schedule. Edit or delete it first."
            ]);
        }

        $section->timetables()->create($data);
        return back()->with('success', "Schedule added: {$data['day_of_week']} {$data['start_time']}–{$data['end_time']}");
    }

    public function update(Request $request, Timetable $timetable)
    {
        $data = $request->validate([
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'room'        => 'nullable|string|max:50',
        ]);

        $timetable->update($data);
        return back()->with('success', 'Schedule updated.');
    }

    public function destroy(Timetable $timetable)
    {
        $sectionId = $timetable->section_id;
        $timetable->delete();

        return redirect()
            ->route('admin.timetable.index', $sectionId)
            ->with('success', 'Schedule slot removed.');
    }
}