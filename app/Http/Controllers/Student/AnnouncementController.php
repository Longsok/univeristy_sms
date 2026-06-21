<?php

namespace App\Http\Controllers\Student;
 
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
 
class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::published()
            ->forRole('student')
            ->latest('published_at')
            ->paginate(10);
 
        return view('student.announcements.index', compact('announcements'));
    }
}