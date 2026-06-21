<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Section, Group, GroupMember, Student};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index(Section $section)
    {
        abort_unless(Auth::user()->teacher->id === $section->teacher_id, 403);

        $groups = $section->groups()->with('members.student.user')->get();

        // Students not yet assigned to any group
        $assignedStudentIds = $groups->flatMap(fn($g) => $g->members->pluck('student_id'));

        $unassigned = $section->enrollments()
            ->with('student.user')
            ->where('status', 'enrolled')
            ->whereNotIn('student_id', $assignedStudentIds)
            ->get()
            ->pluck('student');

        return view('teacher.groups.index', compact('section', 'groups', 'unassigned'));
    }

    public function store(Request $request, Section $section)
    {
        abort_unless(Auth::user()->teacher->id === $section->teacher_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $section->groups()->create($data);

        return back()->with('success', 'Group created.');
    }

    public function assign(Request $request, Group $group)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $data = $request->validate([
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        foreach ($data['student_ids'] as $studentId) {
            GroupMember::firstOrCreate([
                'group_id'   => $group->id,
                'student_id' => $studentId,
            ], ['role' => 'member']);
        }

        return back()->with('success', 'Students assigned to group.');
    }

    public function setLeader(Request $request, Group $group)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        // Reset all to member first
        $group->members()->update(['role' => 'member']);

        // Set new leader
        $group->members()
            ->where('student_id', $data['student_id'])
            ->update(['role' => 'leader']);

        return back()->with('success', 'Group leader updated.');
    }

    public function removeMember(Group $group, Student $student)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $group->members()->where('student_id', $student->id)->delete();

        return back()->with('success', 'Student removed from group.');
    }

    public function destroy(Group $group)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $group->delete();

        return back()->with('success', 'Group deleted.');
    }
}