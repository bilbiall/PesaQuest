<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumVote extends Model
{
    protected $fillable = ['user_id', 'votable_type', 'votable_id', 'value'];

    /** Map of the player's votes for a set of votables: [id => +1|-1]. */
    public static function mapFor(?int $userId, string $type, array $ids): array
    {
        if (!$userId || $ids === []) return [];

        return static::where('user_id', $userId)
            ->where('votable_type', $type)
            ->whereIn('votable_id', $ids)
            ->pluck('value', 'votable_id')
            ->all();
    }
}
