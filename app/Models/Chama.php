<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chama extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'goal_text', 'target_amount',
        'monthly_contribution', 'status', 'creator_id', 'max_members', 'pool_balance',
        'visibility', 'join_code', 'min_level', 'min_credit_score', 'min_savings',
        'loan_interest_rate', 'undistributed_gains',
    ];

    protected $attributes = [
        'undistributed_gains' => 0,
    ];

    /** How many chamas a player may belong to at once. */
    public const MAX_MEMBERSHIPS = 3;

    /** Annual rate used when a chama hasn't voted its own via change_loan_terms —
     *  deliberately cheaper than the individual LoanProduct catalog, since the
     *  whole point of a chama loan is mutual, lower-cost credit. */
    public const DEFAULT_LOAN_INTEREST_RATE = 8.0;

    /** Instant-approval loan ceiling, as a multiple of the borrower's own
     *  total_contributed — anything above needs a member vote. */
    public const INSTANT_LOAN_MULTIPLIER = 1.0;

    /** Consecutive on-time contributions required before a member may borrow. */
    public const LOAN_ELIGIBILITY_STREAK = 2;

    /** A chama loan's fixed term — 3 monthly instalments. */
    public const LOAN_TERM_TICKS = 90;
    public const LOAN_PAYMENT_PERIOD_TICKS = 30;

    /** Consecutive defaults before the group is asked to vote out the member. */
    public const DEFAULTS_BEFORE_REMOVAL_VOTE = 2;

    public function effectiveLoanInterestRate(): float
    {
        return (float) ($this->loan_interest_rate ?? self::DEFAULT_LOAN_INTEREST_RATE);
    }

    /** Share links use the readable slug (/chama/nairobi-investors), never the numeric ID. */
    public function getRouteKey()
    {
        return $this->slug ?: $this->getKey();
    }

    /** Resolve by slug first; old numeric links keep working. */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) return parent::resolveRouteBinding($value, $field);

        $bySlug = static::where('slug', $value)->first();
        return $bySlug ?? (ctype_digit((string) $value) ? static::find($value) : null);
    }

    /** A readable, unique slug from the chama name ("Nairobi Investors" → nairobi-investors, -2, …). */
    public static function freshSlug(string $name): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'chama';
        $candidate = $base;
        $i = 1;
        while (static::where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . (++$i);
        }
        return $candidate;
    }

    public function isPrivate(): bool
    {
        return ($this->visibility ?? 'public') === 'private';
    }

    /** Entry requirements the player must clear to join a PUBLIC chama. Returns error string or null. */
    public function entryRequirementError(User $user): ?string
    {
        $progress = $user->getOrCreateProgress();

        if ((int) ($this->min_level ?? 0) > 0 && (int) ($progress->level ?? 1) < (int) $this->min_level) {
            return "This chama requires level {$this->min_level}+ (you are level " . ($progress->level ?? 1) . ').';
        }
        if ((int) ($this->min_credit_score ?? 0) > 0 && (int) ($progress->credit_score ?? 500) < (int) $this->min_credit_score) {
            return "This chama requires a credit score of {$this->min_credit_score}+ (yours is " . ($progress->credit_score ?? 500) . ').';
        }
        if ((int) ($this->min_savings ?? 0) > 0) {
            $savings = \Illuminate\Support\Facades\Schema::hasTable('savings_schemes')
                ? (int) SavingsScheme::where('user_id', $user->id)->sum('current_amount')
                : 0;
            if ($savings < (int) $this->min_savings) {
                return 'This chama requires Ksh ' . number_format($this->min_savings) . '+ in savings (you have Ksh ' . number_format($savings) . ').';
            }
        }

        return null;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ChamaMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(ChamaMember::class)->where('is_active', true);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(ChamaContribution::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(ChamaProposal::class);
    }

    public function chamaAssets(): HasMany
    {
        return $this->hasMany(ChamaAsset::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(ChamaLoan::class);
    }

    public function dividends(): HasMany
    {
        return $this->hasMany(ChamaDividend::class);
    }

    /** Total principal still owed across every active chama loan — the pool's
     *  real exposure, used to gate withdrawals that would leave it uncovered. */
    public function outstandingChamaLoansTotal(): float
    {
        return (float) $this->loans()->where('status', 'active')->sum('outstanding_balance');
    }

    public function memberCount(): int
    {
        return $this->activeMembers()->count();
    }

    public function totalValue(): int
    {
        $assetValue = $this->chamaAssets()->with('asset')->get()->sum(function ($ca) {
            return ($ca->asset->base_price ?? $ca->purchase_price) * $ca->quantity;
        });
        return $this->pool_balance + $assetValue;
    }

    public function monthlyAssetIncome(): int
    {
        return $this->chamaAssets()->with('asset')->get()->sum(function ($ca) {
            return ($ca->asset->monthly_income ?? 0) * $ca->quantity;
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isMember(User $user): bool
    {
        return $this->activeMembers()->where('user_id', $user->id)->exists();
    }

    public function isFull(): bool
    {
        return $this->memberCount() >= $this->max_members;
    }

    public function getMemberRecord(User $user): ?ChamaMember
    {
        return $this->members()->where('user_id', $user->id)->where('is_active', true)->first();
    }

    /** Recalculate share_pct for all active members weighted by total_contributed */
    public function recalculateShares(): void
    {
        $members = $this->activeMembers()->get();
        $total   = $members->sum('total_contributed');

        if ($total <= 0) {
            // Equal split if nobody has contributed yet
            $equal = $members->count() > 0 ? round(100 / $members->count(), 2) : 0;
            foreach ($members as $m) {
                $m->update(['share_pct' => $equal]);
            }
            return;
        }

        foreach ($members as $m) {
            $pct = round(($m->total_contributed / $total) * 100, 2);
            $m->update(['share_pct' => $pct]);
        }
    }
}
