<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'enrollment_id', 
        'grade_component_id', 
        'score', 
        'reexam_score', 
        'remarks',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(GradeComponent::class, 'grade_component_id');
    }

    // Effective score: reexam takes priority if it exists
    public function getEffectiveScoreAttribute(): float
    {
        return $this->reexam_score ?? $this->score;
    }
}