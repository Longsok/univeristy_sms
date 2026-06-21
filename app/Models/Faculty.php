<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $fillable = [
        'name', 
        'code', 
        'dean_name', 
        'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function programs()
    {
        return $this->hasManyThrough(Program::class, Department::class);
    }
}