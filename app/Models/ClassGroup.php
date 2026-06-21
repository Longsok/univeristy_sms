<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassGroup extends Model
{
    protected $fillable = [
        'program_id',
        'name',
        'description',
        'year_level',
        'batch',          
        'capacity',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    // Full label e.g. "M1 — BCS Year 1 · Batch 3"
    public function getFullLabelAttribute(): string
    {
        $label = "{$this->name} — {$this->program->code} Year {$this->year_level}";
        if ($this->batch) $label .= " · Batch {$this->batch}";
        return $label;
    }

    public function getBatchLabelAttribute(): string
    {
        return $this->batch ? "Batch {$this->batch}" : '';
    }
}