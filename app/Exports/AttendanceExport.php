<?php
namespace App\Exports;

use App\Models\Section;
use App\Services\AttendanceService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class AttendanceExport implements WithMultipleSheets
{
    public function __construct(private Section $section) {}

    public function sheets(): array
    {
        return [
            new AttendanceSummarySheet($this->section),
            new AttendanceDetailSheet($this->section),
        ];
    }
}

// ── Sheet 1: Summary (one row per student) ─────────────────────────────────────
class AttendanceSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private Section $section) {}

    public function collection(): Collection
    {
        $service = app(AttendanceService::class);
        $stats   = $service->getSectionStats($this->section);

        return collect($stats)->map(fn($s) => [
            'student_id'     => $s['student']->student_id,
            'name'           => $s['student']->user->name,
            'total_sessions' => $s['total_sessions'],
            'present'        => $s['present'],
            'late'           => $s['late'],
            'absent'         => $s['absent'],
            'percentage'     => $s['percentage'] . '%',
            'standing'       => strtoupper($s['standing']),
        ]);
    }

    public function headings(): array
    {
        return [
            'Student ID', 'Name', 'Total Sessions',
            'Present', 'Late', 'Absent', 'Attendance %', 'Standing',
        ];
    }

    public function title(): string { return 'Summary'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

// ── Sheet 2: Detail (one row per session per student) ─────────────────────────
class AttendanceDetailSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(private Section $section) {}

    public function collection(): Collection
    {
        $rows     = collect();
        $sessions = $this->section->attendanceSessions()
            ->with('attendanceRecords.student.user')
            ->orderBy('session_date')
            ->get();

        foreach ($sessions as $session) {
            $enrolled = $this->section->enrollments()
                ->with('student.user')
                ->where('status', 'enrolled')
                ->get();

            foreach ($enrolled as $enrollment) {
                $record = $session->attendanceRecords
                    ->firstWhere('student_id', $enrollment->student_id);

                $rows->push([
                    'date'       => $session->session_date->format('d/m/Y'),
                    'student_id' => $enrollment->student->student_id,
                    'name'       => $enrollment->student->user->name,
                    'status'     => $record ? strtoupper($record->status) : 'ABSENT',
                    'scanned_at' => $record?->scanned_at?->format('H:i') ?? '-',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Date', 'Student ID', 'Name', 'Status', 'Time Scanned'];
    }

    public function title(): string { return 'Detail'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}