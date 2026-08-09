<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    protected $fillable = [
        'template_id', 'mode', 'is_team_based', 'is_chama_battle', 'scope', 'is_official', 'creator_id',
        'school_subscription_id', 'school_class_id', 'chama_id', 'title', 'slug', 'metric', 'style', 'goal',
        'requirements', 'stake_amount', 'level_min', 'level_max',
        'starts_at', 'ends_at', 'status',
    ];

    /** Share links use the readable slug (/challenges/net-worth-sprint-x7q2), never the bare numeric ID. */
    public function getRouteKey()
    {
        return ($this->hasSlugColumn() && $this->slug) ? $this->slug : $this->getKey();
    }

    /**
     * Resolve by slug first; old numeric links keep working. Guarded by
     * hasSlugColumn() so an environment that hasn't run the slug migration
     * yet (e.g. a live site pending a deploy) falls back to plain numeric
     * lookup instead of a hard 500 on "Unknown column 'slug'".
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) return parent::resolveRouteBinding($value, $field);

        if ($this->hasSlugColumn()) {
            $bySlug = static::where('slug', $value)->first();
            if ($bySlug) return $bySlug;
        }

        return ctype_digit((string) $value) ? static::find($value) : null;
    }

    /** A readable, unique slug from the challenge title ("Net Worth Sprint" → net-worth-sprint-4f2a). */
    public static function freshSlug(string $title): string
    {
        if (!(new static)->hasSlugColumn()) return '';

        $base = \Illuminate\Support\Str::slug($title) ?: 'challenge';
        $candidate = $base . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4));
        while (static::where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(4));
        }
        return $candidate;
    }

    private function hasSlugColumn(): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn('challenges', 'slug');
    }

    protected $casts = [
        'is_official'     => 'boolean',
        'is_team_based'   => 'boolean',
        'is_chama_battle' => 'boolean',
        'goal'            => 'float',
        'requirements'    => 'array',
        'starts_at'       => 'datetime',
        'ends_at'         => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChallengeTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(SchoolSubscription::class, 'school_subscription_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function chama(): BelongsTo
    {
        return $this->belongsTo(Chama::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function isDuel(): bool
    {
        return $this->mode === 'duel';
    }

    public function isBroadcast(): bool
    {
        return $this->mode === 'broadcast';
    }

    public function styleSuffix(): string
    {
        return match ($this->style) {
            'percent' => '%',
            'amount'  => '',
            default   => '',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Human-readable win-requirement badge text, e.g. "🎯 Must: all bills paid, 2+ assets". */
    public function describeRequirements(): ?string
    {
        return app(\App\Services\ChallengeRequirementChecker::class)->describe($this->requirements ?? []);
    }
}
