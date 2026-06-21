<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Program extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'degree_level',
        'total_credits',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    // Program → Courses → Sections (through courses)
    public function sections(): HasManyThrough
    {
        return $this->hasManyThrough(
            Section::class,
            Course::class,
            'program_id', // FK on courses
            'course_id',  // FK on sections
            'id',         // PK on programs
            'id'          // PK on courses
        );
    }

    // Courses grouped by year level
    public function coursesByYear(): \Illuminate\Support\Collection
    {
        return $this->courses()
            ->with('semester')
            ->orderBy('year_level')
            ->orderBy('semester_id')
            ->get()
            ->groupBy('year_level');
    }
}