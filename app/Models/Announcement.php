<?php
// app/Models/Announcement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'target_role',
        'published_at',
        'attachment',
        'attachment_name',
    ];

    protected $casts = ['published_at' => 'datetime'];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where(fn($q) =>
            $q->where('target_role', 'all')->orWhere('target_role', $role)
        );
    }

    // Get the full URL for the attachment
    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment
            ? Storage::disk('public')->url($this->attachment)
            : null;
    }

    // Check if file is an image
    public function getIsImageAttribute(): bool
    {
        if (!$this->attachment_name) return false;
        $ext = strtolower(pathinfo($this->attachment_name, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}