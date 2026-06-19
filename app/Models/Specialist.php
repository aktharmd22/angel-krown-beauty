<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Specialist extends Model
{
    protected $fillable = [
        'name',
        'role',
        'blurb',
        'photo',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActiveOrdered(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Public URL for the photo. Supports both uploaded files (on the
     * "uploads" disk) and absolute paths from the seeder (e.g. /assets/...).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (blank($this->photo)) {
            return null;
        }

        if (str_starts_with($this->photo, '/') || str_starts_with($this->photo, 'http')) {
            return $this->photo;
        }

        return Storage::disk('uploads')->url($this->photo);
    }

    /**
     * The label shown in the booking dropdown / WhatsApp message.
     */
    public function getOptionLabelAttribute(): string
    {
        return $this->role ? "{$this->name} — {$this->role}" : $this->name;
    }
}
