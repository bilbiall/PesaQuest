<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumReaction extends Model
{
    protected $fillable = ['user_id', 'topic_id', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }
}
