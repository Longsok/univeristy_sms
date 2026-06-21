<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};
 
class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('admin')->latest()->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }
 
    public function create()
    {
        return view('admin.announcements.create');
    }
 
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'body'        => 'required|string',
            'target_role' => 'required|in:all,student,teacher',
            'publish_now' => 'nullable|boolean',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
 
        $attachmentPath = null;
        $attachmentName = null;
 
        if ($request->hasFile('attachment')) {
            $file           = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('announcements', 'public');
        }
 
        Announcement::create([
            'user_id'         => Auth::id(),
            'title'           => $data['title'],
            'body'            => $data['body'],
            'target_role'     => $data['target_role'],
            'published_at'    => $request->boolean('publish_now') ? now() : null,
            'attachment'      => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);
 
        return redirect()->route('admin.announcements.index')
                         ->with('success', 'Announcement saved.');
    }
 
    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }
 
    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'body'        => 'required|string',
            'target_role' => 'required|in:all,student,teacher',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
 
        $updateData = [
            'title'       => $data['title'],
            'body'        => $data['body'],
            'target_role' => $data['target_role'],
        ];
 
        if ($request->hasFile('attachment')) {
            // Delete old attachment
            if ($announcement->attachment) {
                Storage::disk('public')->delete($announcement->attachment);
            }
            $file = $request->file('attachment');
            $updateData['attachment']      = $file->store('announcements', 'public');
            $updateData['attachment_name'] = $file->getClientOriginalName();
        }
 
        if ($request->boolean('remove_attachment') && $announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
            $updateData['attachment']      = null;
            $updateData['attachment_name'] = null;
        }
 
        $announcement->update($updateData);
        return back()->with('success', 'Announcement updated.');
    }
 
    public function publish(Announcement $announcement)
    {
        $announcement->update(['published_at' => now()]);
        return back()->with('success', 'Announcement published.');
    }
 
    public function destroy(Announcement $announcement)
    {
        if ($announcement->attachment) {
            Storage::disk('public')->delete($announcement->attachment);
        }
        $announcement->delete();
        return back()->with('success', 'Announcement deleted.');
    }
}
 