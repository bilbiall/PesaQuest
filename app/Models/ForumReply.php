<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ForumReply extends Model
{
    protected $fillable = [
        'topic_id', 'parent_id', 'user_id', 'body', 'image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    public function parent()
    {
        return $this->belongsTo(ForumReply::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ForumReply::class, 'parent_id')->oldest();
    }

    /**
     * Nests a flat collection of replies (root + descendants, oldest first)
     * into a tree via setRelation('children', ...) — not a plain attribute,
     * so nothing pollutes $attributes if these instances are ever saved.
     */
    public static function buildTree(Collection $replies): Collection
    {
        $byParent = $replies->groupBy('parent_id');

        $attach = function (Collection $nodes) use (&$attach, $byParent) {
            foreach ($nodes as $node) {
                $attach($byParent->get($node->id, collect()));
                $node->setRelation('children', $byParent->get($node->id, collect()));
            }
            return $nodes;
        };

        return $attach($byParent->get(null, collect()));
    }
}
