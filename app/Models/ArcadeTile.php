<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArcadeTile extends Model
{
    protected $fillable = [
        'arcade_game_id', 'number', 'money_effect', 'money_percent',
        'movement_role', 'target_number', 'is_mystery', 'is_golden', 'icon', 'label', 'arcade_sponsor_id',
        'pos_left', 'pos_top', 'pos_left_mobile', 'pos_top_mobile',
    ];

    protected $casts = [
        'is_mystery'      => 'boolean',
        'is_golden'       => 'boolean',
        'pos_left'        => 'float',
        'pos_top'         => 'float',
        'pos_left_mobile' => 'float',
        'pos_top_mobile'  => 'float',
    ];

    public const MONEY_EFFECTS = ['none' => 'None', 'reward' => 'Reward', 'expense' => 'Expense'];
    public const MOVEMENT_ROLES = ['none' => 'None', 'ladder_bottom' => 'Ladder bottom', 'snake_head' => 'Snake head'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(ArcadeGame::class, 'arcade_game_id');
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(ArcadeSponsor::class, 'arcade_sponsor_id');
    }
}
