<?php
// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'program_id',
        'class_group_id',
        'student_id',
        'year_level',
        'batch',           
        'date_of_birth',
        'status',
        'scholarship_type',
    ];

    protected $casts = ['date_of_birth' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function promotionHistories(): HasMany
    {
        return $this->hasMany(PromotionHistory::class);
    }

    public function activeEnrollments()
    {
        return $this->enrollments()
                    ->where('status', 'enrolled')
                    ->with('section.course');
    }

    // Helper: scholarship label
    public function getScholarshipLabelAttribute(): string
    {
        return match($this->scholarship_type) {
            'full'    => 'Full Scholarship',
            'partial' => 'Partial Scholarship',
            default   => 'Self-Funded',
        };
    }

    public function isScholarship(): bool
    {
        return in_array($this->scholarship_type, ['full', 'partial']);
    }

    // Helper: display batch label e.g. "Batch 3"
    public function getBatchLabelAttribute(): string
    {
        return $this->batch ? "Batch {$this->batch}" : 'No Batch';
    }
}