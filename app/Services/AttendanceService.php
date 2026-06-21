<?php
namespace App\Services;

use App\Models\{Section, Student, AttendanceSession, AttendanceRecord};
use Illuminate\Support\Collection;

class AttendanceService
{
    /**
     * Get attendance statistics for all students in a section.
     * Returns array keyed by student_id.
     */
    public function getSectionStats(Section $section): array
    {
        $sessionIds   = $section->attendanceSessions()->pluck('id');
        $totalSessions = $sessionIds->count();

        if ($totalSessions === 0) {
            return [];
        }

        $stats = [];

        $enrollments = $section->enrollments()
            ->with('student.user')
            ->where('status', 'enrolled')
            ->get();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;

            $records = AttendanceRecord::where('student_id', $student->id)
                ->whereIn('attendance_session_id', $sessionIds)
                ->get();

            $present = $records->where('status', 'present')->count();
            $late    = $records->where('status', 'late')->count();
            $absent  = $totalSessions - $records->count(); // sessions with no record = absent

            $attendedCount  = $present + $late;
            $percentage     = round(($attendedCount / $totalSessions) * 100, 1);

            $stats[$student->id] = [
                'student'        => $student,
                'total_sessions' => $totalSessions,
                'present'        => $present,
                'late'           => $late,
                'absent'         => $absent,
                'attended'       => $attendedCount,
                'percentage'     => $percentage,
                'standing'       => $this->attendanceStanding($percentage),
            ];
        }

        return $stats;
    }

    /**
     * Get attendance summary for a single student across all their sections.
     */
    public function getStudentSummary(Student $student): array
    {
        $summary = [];

        $enrollments = $student->enrollments()
            ->with('section.course', 'section.attendanceSessions')
            ->where('status', 'enrolled')
            ->get();

        foreach ($enrollments as $enrollment) {
            $section      = $enrollment->section;
            $sessionIds   = $section->attendanceSessions->pluck('id');
            $totalSessions = $sessionIds->count();

            if ($totalSessions === 0) {
                $summary[] = [
                    'course'         => $section->course->name,
                    'code'           => $section->course->code,
                    'section'        => $section->name,
                    'total_sessions' => 0,
                    'attended'       => 0,
                    'percentage'     => 0,
                    'standing'       => 'no_sessions',
                ];
                continue;
            }

            $records = AttendanceRecord::where('student_id', $student->id)
                ->whereIn('attendance_session_id', $sessionIds)
                ->get();

            $attended   = $records->whereIn('status', ['present', 'late'])->count();
            $percentage = round(($attended / $totalSessions) * 100, 1);

            $summary[] = [
                'course'         => $section->course->name,
                'code'           => $section->course->code,
                'section'        => $section->name,
                'total_sessions' => $totalSessions,
                'present'        => $records->where('status', 'present')->count(),
                'late'           => $records->where('status', 'late')->count(),
                'absent'         => $totalSessions - $records->count(),
                'attended'       => $attended,
                'percentage'     => $percentage,
                'standing'       => $this->attendanceStanding($percentage),
            ];
        }

        return $summary;
    }

    /**
     * Mark a student absent for all open sessions they didn't scan.
     * Called when teacher closes a session.
     */
    public function markAbsentees(AttendanceSession $session): int
    {
        $enrolledStudentIds = $session->section
            ->enrollments()
            ->where('status', 'enrolled')
            ->pluck('student_id');

        $scannedStudentIds = $session->attendanceRecords()->pluck('student_id');

        $absentIds = $enrolledStudentIds->diff($scannedStudentIds);

        foreach ($absentIds as $studentId) {
            AttendanceRecord::create([
                'attendance_session_id' => $session->id,
                'student_id'            => $studentId,
                'scanned_at'            => now(),
                'status'                => 'absent',
                'ip_address'            => null,
            ]);
        }

        return $absentIds->count();
    }

    /**
     * Human-readable attendance standing.
     */
    private function attendanceStanding(float $percentage): string
    {
        return match(true) {
            $percentage >= 80 => 'good',       // green
            $percentage >= 60 => 'warning',    // yellow — at risk
            default           => 'critical',   // red — may be barred
        };
    }
}