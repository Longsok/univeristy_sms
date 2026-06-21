<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    protected $fillable = [
        'section_id', 
        'day_of_week', 
        'start_time', 
        'end_time', 
        'room',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}