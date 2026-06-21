<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'course_id', 
        'teacher_id', 
        'name', 
        'max_students',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function gradeComponents(): HasMany
    {
        return $this->hasMany(GradeComponent::class)->orderBy('sort_order');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    // Enrolled students
    public function students()
    {
        return $this->hasManyThrough(
            Student::class, Enrollment::class,
            'section_id', 'id', 'id', 'student_id'
        );
    }

    // Check if all components weights sum to 100
    public function isGradingConfigured(): bool
    {
        return $this->gradeComponents()->sum('weight_percent') == 100;
    }
}