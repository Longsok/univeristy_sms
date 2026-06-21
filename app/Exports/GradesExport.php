<?php
namespace App\Exports;

use App\Models\Section;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class GradesExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    WithStyles,
    ShouldAutoSize
{
    public function __construct(private Section $section) {}

    public function collection(): Collection
    {
        $components  = $this->section->gradeComponents;
        $enrollments = $this->section->enrollments()
            ->with('student.user', 'grades.component')
            ->where('status', 'enrolled')
            ->get();

        return $enrollments->map(function ($enrollment) use ($components) {
            $row = [
                'student_id'   => $enrollment->student->student_id,
                'name'         => $enrollment->student->user->name,
            ];

            // One column per grade component
            foreach ($components as $component) {
                $grade = $enrollment->grades
                    ->firstWhere('grade_component_id', $component->id);

                $row[$component->name]          = $grade?->score ?? '-';
                $row[$component->name . ' (Re)']= $grade?->reexam_score ?? '-';
            }

            $row['Final Grade']  = $enrollment->final_grade  ?? 'Not finalised';
            $row['Letter Grade'] = $enrollment->letter_grade ?? '-';
            $row['Grade Points'] = $enrollment->grade_points ?? '-';
            $row['Status']       = strtoupper($enrollment->grade_status ?? 'not_graded');

            return $row;
        });
    }

    public function headings(): array
    {
        $components = $this->section->gradeComponents;

        $headers = ['Student ID', 'Name'];

        foreach ($components as $component) {
            $headers[] = $component->name . ' /' . $component->max_score;
            $headers[] = $component->name . ' Re-exam';
        }

        $headers[] = 'Final Grade';
        $headers[] = 'Letter Grade';
        $headers[] = 'Grade Points';
        $headers[] = 'Status';

        return $headers;
    }

    public function title(): string
    {
        return $this->section->course->code . ' ' . $this->section->name;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true],
                'fill'      => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F3864'],
                ],
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}