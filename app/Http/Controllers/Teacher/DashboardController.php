<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Announcement, Semester};
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            abort(403, 'No teacher profile found. Contact the administrator.');
        }

        $teacher->load('department.faculty');

        $sections = $teacher->sections()
            ->with([
                'course.semester',
                'course.program',
                'enrollments',
                'gradeComponents',
            ])
            ->get();

        // Get all currently running semesters
        $runningSemesters = Semester::allRunning();

        $sectionStats = $sections->map(function($section) use ($runningSemesters) {
            $semester      = $section->course?->semester;
            $isInSession   = $semester?->isRunning() ?? false;
            $isUpcoming    = $semester?->isUpcoming() ?? false;

            return [
                'section'        => $section,
                'enrolled_count' => $section->enrollments->where('status', 'enrolled')->count(),
                'graded_count'   => $section->enrollments->where('grades_finalised', true)->count(),
                'reexam_count'   => $section->enrollments->where('grade_status', 'reexam')->count(),
                'is_in_session'  => $isInSession,
                'is_upcoming'    => $isUpcoming,
                'semester_name'  => $semester
                    ? $semester->name . ' ' . $semester->academic_year
                      . ($semester->year_level ? ' (Yr'.$semester->year_level.')' : '')
                    : null,
            ];
        });

        // Sort: in-session first, then upcoming, then completed
        $sectionStats = $sectionStats->sortByDesc('is_in_session')
            ->sortByDesc('is_upcoming')
            ->values();

        $announcements = Announcement::published()
            ->forRole('teacher')
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('teacher.dashboard', compact(
            'teacher', 'sections', 'sectionStats',
            'announcements', 'runningSemesters'
        ));
    }
}