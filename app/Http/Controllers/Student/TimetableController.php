<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TimetableController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        $enrollments = $student->enrollments()
            ->with('section.timetables', 'section.course', 'section.teacher.user')
            ->where('status', 'enrolled')
            ->get();

        // Build timetable grid keyed by day
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $timetable = collect($days)->mapWithKeys(fn($day) => [
            $day => $enrollments->flatMap(fn($e) =>
                $e->section->timetables->where('day_of_week', $day)->map(fn($t) => [
                    'course'     => $e->section->course->name,
                    'code'       => $e->section->course->code,
                    'section'    => $e->section->name,
                    'teacher'    => $e->section->teacher?->user?->name ?? 'TBA',
                    'start_time' => $t->start_time,
                    'end_time'   => $t->end_time,
                    'room'       => $t->room ?? 'TBA',
                ])
            )->sortBy('start_time')->values(),
        ]);

        return view('student.timetable.index', compact('timetable', 'days'));
    }
}