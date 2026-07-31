<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScenarioRating extends Model
{
    protected $fillable = ['node_id', 'user_id', 'rating'];

    public function node() { return $this->belongsTo(Node::class); }
    public function user() { return $this->belongsTo(User::class); }

    public static function summaryFor(int $nodeId): array
    {
        $rows = self::where('node_id', $nodeId)->selectRaw('rating, count(*) as cnt')->groupBy('rating')->pluck('cnt', 'rating');
        return [
            'up'    => (int)($rows[1]  ?? 0),
            'down'  => (int)($rows[-1] ?? 0),
        ];
    }
}
