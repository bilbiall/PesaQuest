<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $fillable = [
        'title', 'description', 'instructions', 'lesson', 'xp_reward', 'kes_reward',
        'icon', 'image', 'age_group', 'career_fields', 'is_active', 'sort_order',
        'level_required', 'trigger_type', 'trigger_value', 'trigger_label', 'triggers',
        'trigger_mode', 'source', 'blueprint_id', 'blueprint_slot',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'triggers'      => 'array',
        'trigger_mode'  => 'string',
        'career_fields' => 'array',
    ];

    public function userQuests()
    {
        return $this->hasMany(UserQuest::class);
    }

    public function userQuestFor(int $userId): ?UserQuest
    {
        return $this->userQuests()->where('user_id', $userId)->first();
    }

    public function isMultiTrigger(): bool
    {
        return !empty($this->triggers) && count($this->triggers) > 0;
    }

    /** True if this quest targets every career path (the default). */
    public function isAllCareerPaths(): bool
    {
        return empty($this->career_fields);
    }

    /** True if the given career field (or no field chosen yet) may see this quest. */
    public function matchesCareerField(?string $field): bool
    {
        if ($this->isAllCareerPaths()) return true;
        return $field !== null && in_array($field, $this->career_fields, true);
    }

    /** Scope: quests visible to a player's chosen career field (or path-agnostic quests). */
    public function scopeForCareerField($query, ?string $field)
    {
        return $query->where(function ($q) use ($field) {
            $q->whereNull('career_fields')->orWhereJsonLength('career_fields', 0);
            if ($field !== null) {
                $q->orWhereJsonContains('career_fields', $field);
            }
        });
    }
}
