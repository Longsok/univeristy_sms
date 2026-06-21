<?php

namespace App\Http\Controllers\Teacher;
 
use App\Http\Controllers\Controller;
use App\Models\Announcement;
 
class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::published()
            ->forRole('teacher')
            ->latest('published_at')
            ->paginate(10);
 
        return view('teacher.announcements.index', compact('announcements'));
    }
}