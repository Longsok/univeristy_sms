<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    protected $fillable = [
        'student_id',
        'section_id',
        'status',
        'final_grade',
        'letter_grade',
        'grade_points',
        'grade_status',
        'grades_finalised',
        'grades_finalised_at',
    ];

    protected $casts = [
        'grades_finalised'    => 'boolean',
        'grades_finalised_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * FIXED: scores are entered directly as weighted points
     * e.g. Attendance worth 10pts → teacher enters 0–10 directly
     * Final grade = sum of all scores
     */
    public function computeWeightedGrade(): float
    {
        $components = $this->section->gradeComponents;
        $total = 0;

        foreach ($components as $component) {
            $grade = $this->grades()
                          ->where('grade_component_id', $component->id)
                          ->first();

            if ($grade) {
                // Use reexam score if available, otherwise regular score
                $score = $grade->reexam_score ?? $grade->score;

                // Clamp to max (weight_percent) — direct sum
                $total += min((float) $score, (float) $component->weight_percent);
            }
        }

        return round($total, 2);
    }

    /**
     * Check if all required components have grades
     */
    public function isComplete(): bool
    {
        $componentCount = $this->section->gradeComponents()->count();
        $gradedCount    = $this->grades()->count();
        return $componentCount > 0 && $gradedCount >= $componentCount;
    }
}