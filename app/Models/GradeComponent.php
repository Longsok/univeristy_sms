<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeComponent extends Model
{
    protected $fillable = [
        'section_id', 
        'name', 
        'max_score', 
        'weight_percent',
        'is_reexam_component', 
        'sort_order',
    ];

    protected $casts = [
        'is_reexam_component' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}