<?php
// app/Models/PromotionHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionHistory extends Model
{
    protected $fillable = [
        'student_id',
        'class_group_id',
        'promoted_by',
        'from_year',
        'to_year',
        'type',
        'academic_year',
        'gpa_at_promotion',
        'notes',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function promotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}