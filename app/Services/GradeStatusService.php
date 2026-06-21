<?php
namespace App\Services;

use App\Models\Enrollment;

class GradeStatusService
{
    // ── Grading thresholds ────────────────────────────────────────────────────
    private float $passThreshold   = 50.0; // ≥ 50 = Pass (C and above)
    private float $reexamThreshold = 45.0; // 45–49 = D = Re-exam eligible

    // ── RUPP Grade Table ──────────────────────────────────────────────────────
    // A    4.0   85–100
    // B+   3.5   80–84
    // B    3.0   70–79
    // C+   2.5   65–69
    // C    2.0   50–64
    // D    1.5   45–49  → Re-exam
    // F    0.0   0–44   → Fail
    private array $gradeTable = [
        ['min' => 85, 'max' => 100, 'letter' => 'A',  'points' => 4.0],
        ['min' => 80, 'max' => 84,  'letter' => 'B+', 'points' => 3.5],
        ['min' => 70, 'max' => 79,  'letter' => 'B',  'points' => 3.0],
        ['min' => 65, 'max' => 69,  'letter' => 'C+', 'points' => 2.5],
        ['min' => 50, 'max' => 64,  'letter' => 'C',  'points' => 2.0],
        ['min' => 45, 'max' => 49,  'letter' => 'D',  'points' => 1.5],
        ['min' => 0,  'max' => 44,  'letter' => 'F',  'points' => 0.0],
    ];

    public function finalise(Enrollment $enrollment): void
    {
        if (!$enrollment->isComplete()) {
            $enrollment->update([
                'grade_status'        => 'incomplete',
                'grades_finalised'    => true,
                'grades_finalised_at' => now(),
            ]);
            return;
        }

        $finalGrade  = $enrollment->computeWeightedGrade();
        $letterGrade = $this->toLetter($finalGrade);
        $gradePoints = $this->toPoints($finalGrade);
        $status      = $this->determineStatus($finalGrade);

        $enrollment->update([
            'final_grade'         => $finalGrade,
            'letter_grade'        => $letterGrade,
            'grade_points'        => $gradePoints,
            'grade_status'        => $status,
            'grades_finalised'    => true,
            'grades_finalised_at' => now(),
        ]);
    }

    public function determineStatus(float $grade): string
    {
        if ($grade >= $this->passThreshold)   return 'pass';
        if ($grade >= $this->reexamThreshold) return 'reexam';
        return 'fail';
    }

    public function toLetter(float $grade): string
    {
        foreach ($this->gradeTable as $row) {
            if ($grade >= $row['min'] && $grade <= $row['max']) {
                return $row['letter'];
            }
        }
        return 'F';
    }

    public function toPoints(float $grade): float
    {
        foreach ($this->gradeTable as $row) {
            if ($grade >= $row['min'] && $grade <= $row['max']) {
                return $row['points'];
            }
        }
        return 0.0;
    }
}