<?php
// app/Models/AttendanceRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'section_id',
        'student_id',
        'week',
        'status', // present, absent, late, permission
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // ── Score calculation ─────────────────────────────────
    // Scoring out of 10 for 16 weeks:
    // present    = 1.0 point
    // late       = 0.5 point
    // permission = 0.5 point
    // absent     = 0.0 point
    public static function calculateScore(array $records): float
    {
        $total = 0;
        foreach ($records as $record) {
            $total += match($record->status) {
                'present'    => 1.0,
                'late'       => 0.5,
                'permission' => 0.5,
                'absent'     => 0.0,
                default      => 0.0,
            };
        }
        // 16 weeks = 16 points max → scale to 10
        return round(min(10, ($total / 16) * 10), 2);
    }
}