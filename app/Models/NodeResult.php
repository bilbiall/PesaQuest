<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NodeResult extends Model
{
    protected $fillable = ['node_id', 'result_text', 'lesson_text', 'illustration'];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
