<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AttendanceSession extends Model
{
    protected $fillable = [
        'section_id', 
        'session_date', 
        'qr_token', 
        'qr_expires_at', 
        'is_open',
    ];

    protected $casts = [
        'session_date'  => 'date',
        'qr_expires_at' => 'datetime',
        'is_open'       => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function generateQrToken(int $minutesValid = 15): void
    {
        $this->update([
            'qr_token'      => Str::random(48),
            'qr_expires_at' => now()->addMinutes($minutesValid),
            'is_open'       => true,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->qr_expires_at && $this->qr_expires_at->isPast();
    }
}