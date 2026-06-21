<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Semester extends Model
{
    protected $fillable = [
        'name',
        'academic_year',
        'semester_number',
        'year_level',      // NULL = all years, 1-6 = specific year
        'start_date',
        'end_date',
        'is_active',       // true = belongs to current academic year
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    // ── Date-based status (only meaningful in current academic year) ──────────

    public function isRunning(): bool
    {
        // Only show as running if it belongs to the current academic year
        if (!$this->is_active) return false;
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function isUpcoming(): bool
    {
        if (!$this->is_active) return false;
        return $this->start_date > now();
    }

    public function isCompleted(): bool
    {
        // Past academic years are always completed
        if (!$this->is_active) return true;
        return $this->end_date < now();
    }

    public function getProgressAttribute(): int
    {
        if (!$this->isRunning()) return 0;
        $total   = max(1, $this->start_date->diffInDays($this->end_date));
        $elapsed = $this->start_date->diffInDays(now());
        return min(100, (int) round(($elapsed / $total) * 100));
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    public static function currentAcademicYear(): ?string
    {
        return Cache::remember('current_academic_year', 300, function () {
            return self::where('is_active', true)->value('academic_year');
        });
    }

    /**
     * For admin topbar — show the most relevant running semester
     */
    public static function current(): ?self
    {
        $year = self::currentAcademicYear();
        if (!$year) return null;

        // Find any currently running semester in the active year
        return self::where('academic_year', $year)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('year_level')
            ->orderByDesc('semester_number')
            ->first()
            // Fall back to upcoming
            ?? self::where('academic_year', $year)
                ->where('is_active', true)
                ->where('start_date', '>', now())
                ->orderBy('start_date')
                ->first();
    }

    /**
     * Get the current semester for a specific year level
     * Used for student/teacher dashboard topbar
     */
    public static function forYearLevel(int $yearLevel): ?self
    {
        $year = self::currentAcademicYear();
        if (!$year) return null;

        // Year-specific running semester
        return self::where('academic_year', $year)
            ->where('is_active', true)
            ->where('year_level', $yearLevel)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('semester_number')
            ->first()
            // Fall back: general semester (null year_level) running
            ?? self::where('academic_year', $year)
                ->where('is_active', true)
                ->whereNull('year_level')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->orderByDesc('semester_number')
                ->first()
            // Fall back: upcoming for that year level
            ?? self::where('academic_year', $year)
                ->where('is_active', true)
                ->where('year_level', $yearLevel)
                ->where('start_date', '>', now())
                ->orderBy('start_date')
                ->first();
    }

    /**
     * Get all running semesters in current year (for display)
     */
    public static function allRunning(): \Illuminate\Support\Collection
    {
        $year = self::currentAcademicYear();
        if (!$year) return collect();

        return self::where('academic_year', $year)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('year_level')
            ->get();
    }
}