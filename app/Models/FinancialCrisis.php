<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialCrisis extends Model
{
    protected $fillable = [
        'name', 'description', 'icon', 'effect_type', 'effect_amount',
        'is_percentage', 'warning_at', 'warning_sent_at', 'active_from', 'active_until',
        'is_processed', 'created_by',
    ];

    protected $casts = [
        'warning_at'      => 'datetime',
        'warning_sent_at' => 'datetime',
        'active_from'     => 'datetime',
        'active_until'    => 'datetime',
        'is_processed'    => 'boolean',
        'is_percentage'   => 'boolean',
    ];

    public const EFFECT_TYPES = [
        'investment_drop' => ['label' => 'Investment Drop', 'icon' => '📉', 'hint' => 'Pending investment deals lose value'],
        'asset_drop'      => ['label' => 'Asset Crash',     'icon' => '🏚️', 'hint' => 'Owned asset values fall'],
        'balance_drain'   => ['label' => 'Balance Drain',   'icon' => '💸', 'hint' => 'Cash wallets lose a percentage'],
        'salary_cut'      => ['label' => 'Salary Cut',      'icon' => '✂️', 'hint' => 'Job salaries reduced while the crisis lasts'],
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeActive($query)
    {
        return $query->where('active_from', '<=', now())
                     ->where('active_until', '>=', now());
    }

    public function scopeWarningDue($query)
    {
        return $query->where('warning_at', '<=', now())
                     ->where('active_from', '>', now())
                     ->whereNull('warning_sent_at')
                     ->where('is_processed', false);
    }

    public function isActive(): bool
    {
        return now()->between($this->active_from, $this->active_until);
    }

    /** scheduled → warned → active → done, for status badges in admin/gameset UIs. */
    public function statusKey(): string
    {
        if ($this->is_processed || now()->gt($this->active_until)) return 'done';
        if ($this->isActive())                                     return 'active';
        if ($this->warning_sent_at)                                return 'warned';
        return 'scheduled';
    }

    public function statusLabel(): string
    {
        return match ($this->statusKey()) {
            'done'      => '✅ Completed',
            'active'    => '🔥 Active now',
            'warned'    => '📢 Warning sent',
            default     => '🗓️ Scheduled',
        };
    }

    public function effectLabel(): string
    {
        $meta = self::EFFECT_TYPES[$this->effect_type] ?? null;
        $amt  = $this->is_percentage ? rtrim(rtrim(number_format($this->effect_amount, 2), '0'), '.') . '%' : 'Ksh ' . number_format($this->effect_amount);
        return ($meta ? $meta['icon'] . ' ' . $meta['label'] : $this->effect_type) . ' · ' . $amt;
    }
}
