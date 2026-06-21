<?php
namespace App\Services;

use App\Models\Student;

class GpaCalculatorService
{
    public function calculate(Student $student): array
    {
        $totalPoints  = 0;
        $totalCredits = 0;
        $courses      = [];

        foreach ($student->enrollments as $enrollment) {
            if (!$enrollment->grades_finalised || !$enrollment->final_grade) continue;

            $course   = $enrollment->section->course;
            $credits  = $course->credit_units;
            $semester = $course->semester;

            $totalPoints  += ($enrollment->grade_points ?? 0) * $credits;
            $totalCredits += $credits;

            // Build semester label — include year_level if semester is year-specific
            $semLabel = $semester
                ? $this->semesterLabel($semester)
                : 'Unknown Semester';

            $courses[] = [
                'course'        => $course->name,
                'code'          => $course->code,
                'semester'      => $semLabel,
                'semester_num'  => $semester?->semester_number ?? 0,
                'academic_year' => $semester?->academic_year ?? '',
                'year_level'    => $course->year_level,
                'credits'       => $credits,
                'final_grade'   => $enrollment->final_grade,
                'letter_grade'  => $enrollment->letter_grade,
                'grade_points'  => $enrollment->grade_points,
                'grade_status'  => $enrollment->grade_status,
            ];
        }

        // Sort by academic year then semester number
        usort($courses, fn($a, $b) =>
            $a['academic_year'] <=> $b['academic_year']
            ?: $a['semester_num'] <=> $b['semester_num']
        );

        $gpa = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;

        return [
            'gpa'           => $gpa,
            'total_credits' => $totalCredits,
            'courses'       => $courses,
        ];
    }

    private function semesterLabel(\App\Models\Semester $semester): string
    {
        $name = $semester->name ?? "Semester {$semester->semester_number}";
        $year = $semester->academic_year;
        return "{$name} — {$year}";
    }
}