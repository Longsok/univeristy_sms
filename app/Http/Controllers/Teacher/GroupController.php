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
            'name'              => 'required|string|max:100',
            'initial_members'   => 'nullable|array',
            'initial_members.*' => 'exists:students,id',
        ]);

        $group = $section->groups()->create(['name' => $data['name']]);

        // Add initial members if selected
        if (!empty($data['initial_members'])) {
            foreach ($data['initial_members'] as $studentId) {
                GroupMember::firstOrCreate([
                    'group_id'   => $group->id,
                    'student_id' => $studentId,
                ], ['role' => 'member']);
            }
            $count = count($data['initial_members']);
            return back()->with('success', "Group \"{$data['name']}\" created with {$count} member(s).");
        }

        return back()->with('success', "Group \"{$data['name']}\" created.");
    }

    /**
     * Rename a group
     */
    public function rename(Request $request, Group $group)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $group->update($data);

        return back()->with('success', "Group renamed to \"{$data['name']}\".");
    }

    public function assign(Request $request, Group $group)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $data = $request->validate([
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $count = 0;
        foreach ($data['student_ids'] as $studentId) {
            GroupMember::firstOrCreate([
                'group_id'   => $group->id,
                'student_id' => $studentId,
            ], ['role' => 'member']);
            $count++;
        }

        return back()->with('success', "{$count} student(s) added to {$group->name}.");
    }

    public function setLeader(Request $request, Group $group)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $group->members()->update(['role' => 'member']);
        $group->members()->where('student_id', $data['student_id'])->update(['role' => 'leader']);

        return back()->with('success', 'Group leader updated.');
    }

    public function removeMember(Group $group, Student $student)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $group->members()->where('student_id', $student->id)->delete();

        return back()->with('success', "{$student->user->name} removed from group.");
    }

    public function destroy(Group $group)
    {
        abort_unless(Auth::user()->teacher->id === $group->section->teacher_id, 403);

        $name = $group->name;
        $group->delete();

        return back()->with('success', "Group \"{$name}\" deleted.");
    }
}