<?php

namespace App\Models;

use App\Notifications\VerifyEmailAutoLogin;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /** Kenya's 47 counties, for the optional player-supplied `county` field. */
    public const COUNTIES = [
        'Baringo', 'Bomet', 'Bungoma', 'Busia', 'Elgeyo-Marakwet', 'Embu', 'Garissa',
        'Homa Bay', 'Isiolo', 'Kajiado', 'Kakamega', 'Kericho', 'Kiambu', 'Kilifi',
        'Kirinyaga', 'Kisii', 'Kisumu', 'Kitui', 'Kwale', 'Laikipia', 'Lamu', 'Machakos',
        'Makueni', 'Mandera', 'Marsabit', 'Meru', 'Migori', 'Mombasa', "Murang'a",
        'Nairobi', 'Nakuru', 'Nandi', 'Narok', 'Nyamira', 'Nyandarua', 'Nyeri',
        'Samburu', 'Siaya', 'Taita-Taveta', 'Tana River', 'Tharaka-Nithi', 'Trans Nzoia',
        'Turkana', 'Uasin Gishu', 'Vihiga', 'Wajir', 'West Pokot',
    ];

    protected $fillable = [
        'name', 'username', 'email', 'password', 'google_id',
        'date_of_birth', 'county', 'age_group', 'is_admin', 'is_gameset', 'is_active',
        'profile_photo', 'cover_photo', 'bio', 'notification_prefs',
        'onboarding_completed_at', 'friend_code',
    ];

    /** Usernames must start with a letter so they can never collide with numeric IDs in URLs. */
    public const USERNAME_REGEX = '/^[a-z][a-z0-9_]{2,19}$/';

    public const RESERVED_USERNAMES = ['admin', 'moski', 'pesaquest', 'system', 'support', 'teacher', 'gameset', 'player'];

    private static ?bool $usernamesEnabled = null;

    public static function usernamesEnabled(): bool
    {
        return self::$usernamesEnabled ??= \Illuminate\Support\Facades\Schema::hasColumn('users', 'username');
    }

    /** URLs use @username when available — numeric IDs never leak into share links. */
    public function getRouteKey()
    {
        return (static::usernamesEnabled() && $this->username) ? $this->username : $this->getKey();
    }

    /** Resolve /players/{x} etc. by username first, falling back to numeric ID (old links keep working). */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field) return parent::resolveRouteBinding($value, $field);

        if (static::usernamesEnabled() && !ctype_digit((string) $value)) {
            return static::where('username', strtolower((string) $value))->first();
        }

        $byId = ctype_digit((string) $value) ? static::find($value) : null;
        return $byId ?? (static::usernamesEnabled() ? static::where('username', strtolower((string) $value))->first() : null);
    }

    /** "@handle" for display, or empty string pre-migration. */
    public function getHandleAttribute(): string
    {
        return $this->username ? '@' . $this->username : '';
    }

    /** Generate a valid unique username from the display name (used at registration/backstop). */
    public function ensureUsername(): ?string
    {
        if (!static::usernamesEnabled()) return null;
        if ($this->username) return $this->username;

        $base = strtolower(\Illuminate\Support\Str::slug($this->name ?? '', '_'));
        $base = preg_replace('/[^a-z0-9_]/', '', $base);
        if ($base === '' || !preg_match('/^[a-z]/', $base)) $base = 'player' . $base;
        $base = substr($base, 0, 20);
        if (strlen($base) < 3) $base = str_pad($base, 3, '0');

        $candidate = $base;
        $i = 1;
        while (static::where('username', $candidate)->exists()) {
            $suffix    = (string) (++$i);
            $candidate = substr($base, 0, 20 - strlen($suffix)) . $suffix;
        }

        $this->username = $candidate;
        $this->save();

        return $candidate;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_admin' => 'boolean',
            'is_gameset' => 'boolean',
            'is_active' => 'boolean',
            'notification_prefs' => 'array',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function progress()
    {
        return $this->hasOne(UserProgress::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')->withTimestamps();
    }

    public function streak()
    {
        return $this->hasOne(UserStreak::class);
    }

    /** Accepted friends, whichever direction the request was sent. */
    public function friends(): \Illuminate\Support\Collection
    {
        $ids = $this->friendIds();
        return $ids === [] ? collect() : User::whereIn('id', $ids)->get();
    }

    /** @return int[] user ids of accepted friends */
    public function friendIds(): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('friendships')) return [];

        return Friendship::where('status', 'accepted')
            ->where(fn ($q) => $q->where('requester_id', $this->id)->orWhere('addressee_id', $this->id))
            ->get(['requester_id', 'addressee_id'])
            ->map(fn ($f) => $f->requester_id === $this->id ? $f->addressee_id : $f->requester_id)
            ->all();
    }

    public function isFriendsWith(User $other): bool
    {
        return Friendship::areFriends($this->id, $other->id);
    }

    /**
     * Render-safe avatar URL. Normalizes legacy '/storage/…' photo paths to
     * '/uploads/…' (the storage symlink is unreliable on shared hosting —
     * app:fix-storage-images copies the files; this keeps old rows rendering).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $p = $this->profile_photo;
        if (!$p) return null;

        $path = preg_replace('#^https?://[^/]+#', '', $p);
        $path = preg_replace('#^/?storage/#', '/uploads/', $path);

        return str_starts_with($path, '/') ? $path : '/' . $path;
    }

    /** Short shareable code (e.g. PQ-7K2M9F) for adding friends without typing names. */
    public function ensureFriendCode(): string
    {
        if ($this->friend_code) return $this->friend_code;

        do {
            $code = 'PQ-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (User::where('friend_code', $code)->exists());

        $this->friend_code = $code;
        $this->save();

        return $code;
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function playerAssets()
    {
        return $this->hasMany(PlayerAsset::class);
    }

    public function getPortfolioValueAttribute(): int
    {
        return $this->playerAssets?->sum('current_value') ?? 0;
    }

    /**
     * True if some subscription is genuinely in effect right now. Checks every
     * row, not just the most-recently-created one — a renewal purchased while
     * a plan is still running is stacked (starts later), so the OLDER row is
     * often the one that's actually active. See activeSubscription().
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /** The subscription actually in effect right now (started, not ended, not paused), if any. */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()->get()->first(fn ($s) => $s->isActive());
    }

    /** A paid-for, stacked renewal that hasn't started yet (queued behind the active one). */
    public function upcomingSubscription(): ?Subscription
    {
        return $this->subscriptions()->get()->first(fn ($s) => $s->isUpcoming());
    }

    public function canAccessNode(Node $node): bool
    {
        if ($node->is_free) return true;
        if (\App\Models\Setting::get('free_for_all', '0') === '1') return true;
        if ($this->hasActiveSchoolMembership()) return true;
        return $this->hasActiveSubscription();
    }

    public function hasActiveSchoolMembership(): bool
    {
        return \App\Models\SchoolMember::where('user_id', $this->id)
            ->where('status', 'active')
            ->whereHas('schoolSubscription', function ($q) {
                $q->where('status', 'active')->where('ends_at', '>', now());
            })
            ->exists();
    }

    public function schoolTeacherRoles()
    {
        return $this->hasMany(\App\Models\SchoolTeacher::class);
    }

    /** True if this account is an accepted teacher/owner at any school. */
    public function getIsSchoolTeacherAttribute(): bool
    {
        return \App\Models\SchoolTeacher::where('user_id', $this->id)->where('status', 'active')->exists();
    }

    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    public function gameNotifications()
    {
        return $this->hasMany(GameNotification::class)->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(GameNotification::class)->where('is_read', false);
    }

    public function getOrCreateProgress(): UserProgress
    {
        // The lazy-loaded relation caches null for brand-new users, so a plain
        // `$this->progress ?? create(...)` creates a DUPLICATE row on every call
        // within the same request. Always re-query, then cache the instance.
        if ($this->relationLoaded('progress') && $this->getRelation('progress')) {
            return $this->getRelation('progress');
        }

        $progress = $this->progress()->first() ?? UserProgress::create([
            'user_id' => $this->id,
            'points_total' => 0,
            'balance' => 0,
            'level' => 1,
        ]);

        $this->setRelation('progress', $progress);

        return $progress;
    }

    public static function ageGroupFromDob(\Carbon\Carbon $dob): string
    {
        $age = $dob->age;
        return match(true) {
            $age <= 12 => '8-12',
            $age <= 17 => '13-17',
            $age <= 25 => '18-25',
            default    => '26+',
        };
    }

    /**
     * Send the auto-login email verification notification.
     * The link logs the user in directly — no prior auth required.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailAutoLogin());
    }
}
