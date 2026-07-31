<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $fillable = [
        'age_group', 'name', 'slug', 'description', 'flavor_text', 'consequence_text',
        'amount', 'frequency_ticks', 'category', 'icon',
        'is_essential', 'credit_impact_pay', 'credit_impact_miss',
        'auto_assign', 'trigger', 'min_chapter', 'is_active',
    ];

    protected $casts = [
        'is_essential'  => 'boolean',
        'auto_assign'   => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function playerBills()
    {
        return $this->hasMany(PlayerBill::class);
    }

    /** Human label for the billing cycle. */
    public function frequencyLabel(): string
    {
        return match(true) {
            $this->frequency_ticks <= 7  => 'Weekly',
            $this->frequency_ticks <= 14 => 'Fortnightly',
            $this->frequency_ticks <= 30 => 'Monthly',
            $this->frequency_ticks <= 90 => 'Termly',
            default                      => 'Annually',
        };
    }
}
