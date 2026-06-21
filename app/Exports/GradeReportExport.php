<?php
namespace App\Exports;

use App\Models\Section;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithTitle, WithStyles};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(private Section $section) {}

    public function collection()
    {
        return $this->section->enrollments()
            ->with('student.user')
            ->where('status', 'enrolled')
            ->orderBy('grade_status')
            ->get()
            ->map(fn($e) => [
                'Student ID'   => $e->student->student_id,
                'Name'         => $e->student->user->name,
                'Final Grade'  => $e->final_grade ?? 'N/A',
                'Letter Grade' => $e->letter_grade ?? 'N/A',
                'Grade Points' => $e->grade_points ?? 'N/A',
                'Status'       => strtoupper($e->grade_status),
            ]);
    }

    public function headings(): array
    {
        return ['Student ID', 'Name', 'Final Grade', 'Letter Grade', 'Grade Points', 'Status'];
    }

    public function title(): string
    {
        return $this->section->course->code . ' - ' . $this->section->name;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}