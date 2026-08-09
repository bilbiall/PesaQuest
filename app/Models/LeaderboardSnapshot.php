<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaderboardSnapshot extends Model
{
    protected $fillable = ['user_id', 'scope_key', 'rank', 'points', 'snapshot_date'];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
