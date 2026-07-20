<?php

namespace App\Models;

use App\Models\JobPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillCategory extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'name_bn',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function getLocalNameAttribute(): string
    {
        $locale = app()->getLocale();
        return match ($locale) {
            'ar'    => $this->name_ar ?? $this->name_en,
            'bn'    => $this->name_bn ?? $this->name_en,
            default => $this->name_en,
        };
    }
}