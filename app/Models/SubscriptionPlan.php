<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['key', 'name', 'plan_type', 'months', 'seats', 'price_kes', 'is_active', 'is_featured', 'description'];

    protected $casts = ['is_active' => 'boolean', 'is_featured' => 'boolean'];

    public function isSchool(): bool
    {
        return $this->plan_type === 'school';
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function transactions()
    {
        return $this->hasMany(MpesaTransaction::class, 'subscription_plan_id');
    }

    public function formattedPrice(): string
    {
        return 'Ksh ' . number_format($this->price_kes);
    }

    public function durationLabel(): string
    {
        return $this->months === 1 ? '1 Month' : "{$this->months} Months";
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public static function active()
    {
        return static::where('is_active', true)->orderBy('months');
    }
}
