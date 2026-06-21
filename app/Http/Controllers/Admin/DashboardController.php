<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Course, Faculty, Department, Program, Semester, Enrollment};

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalStudents'   => User::where('role', 'student')->count(),
            'totalTeachers'   => User::where('role', 'teacher')->count(),
            'totalCourses'    => Course::count(),
            'totalFaculties'  => Faculty::count(),
            'totalDepartments'=> Department::count(),
            'totalPrograms'   => Program::count(),
            'activeSemester'  => Semester::current(),
            'recentUsers'     => User::latest()->take(5)->get(),
            'announcements'   => \App\Models\Announcement::published()
                                      ->forRole('all')->latest('published_at')->take(5)->get(),
        ]);
    }
}