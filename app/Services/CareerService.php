<?php
namespace App\Services;

use App\Models\Setting;
use App\Models\UserProgress;

/**
 * Career fields (the career quiz's interest categories, e.g. "Finance & Banking")
 * and career tracks (the coarser grouping used to recommend courses/jobs, e.g.
 * "finance") are both admin-configurable via GameSet Hub → Career Fields & Tracks.
 * They are stored as Settings JSON and merged over these defaults, exactly like
 * UserProgress::chapters(). Nothing about them is hardcoded anywhere else in the
 * app — CareerService is the single source of truth.
 */
class CareerService
{
    /** Fallback field set — used only until an admin saves custom ones. */
    const DEFAULT_FIELDS = [
        ['key' => 'finance',     'label' => 'Finance & Banking',  'icon' => '🏦', 'color' => '#6366f1', 'track' => 'finance',
            'desc' => "Money management is your superpower! You have an analytical mind and love for making money work smarter. Banking, accounting, investments, and financial planning are your domain."],
        ['key' => 'technology',  'label' => 'Technology',         'icon' => '💻', 'color' => '#8b5cf6', 'track' => 'tech',
            'desc' => "You are a digital innovator! Your curiosity for how things work and your love for solving problems through tech makes you perfect for careers like software development, IT, and digital entrepreneurship."],
        ['key' => 'agriculture', 'label' => 'Agriculture',        'icon' => '🌾', 'color' => '#10b981', 'track' => 'business',
            'desc' => "You connect with the earth and see its potential. Modern agribusiness, food tech, and sustainable farming are massive opportunities waiting for your entrepreneurial spirit."],
        ['key' => 'healthcare',  'label' => 'Healthcare',         'icon' => '🏥', 'color' => '#ec4899', 'track' => 'business',
            'desc' => "You're a natural healer and carer. Your passion for people's wellbeing and your interest in science point to a rewarding career in medicine, nursing, public health, or clinical services."],
        ['key' => 'business',    'label' => 'Business',           'icon' => '🏢', 'color' => '#f59e0b', 'track' => 'business',
            'desc' => "You are an entrepreneur at heart. You see opportunities where others see problems. Starting your own business, leading organizations, or building brands is your natural path."],
        ['key' => 'creative',    'label' => 'Creative Arts',      'icon' => '🎨', 'color' => '#f43f5e', 'track' => 'creative',
            'desc' => "You see the world differently! Your artistic vision and drive to express ideas make you ideal for graphic design, photography, film, music production, and creative direction."],
        ['key' => 'education',   'label' => 'Education',          'icon' => '📚', 'color' => '#0ea5e9', 'track' => 'business',
            'desc' => "You're a born teacher and mentor. Your patience, passion for knowledge, and desire to see others grow point to a fulfilling career in education, training, and curriculum development."],
        ['key' => 'media',       'label' => 'Media & Journalism', 'icon' => '📰', 'color' => '#a855f7', 'track' => 'creative',
            'desc' => "You have a powerful voice and the world needs to hear it! Journalism, content creation, social media, broadcasting, and digital storytelling are where you'll shine."],
        ['key' => 'engineering', 'label' => 'Engineering',        'icon' => '🔧', 'color' => '#64748b', 'track' => 'tech',
            'desc' => "You build things that last. Your analytical mind and love for solving physical problems make you a natural engineer — whether civil, mechanical, electrical, or software-based."],
        ['key' => 'law',         'label' => 'Law & Justice',      'icon' => '⚖️', 'color' => '#d97706', 'track' => 'business',
            'desc' => "You have a strong sense of right and wrong and the courage to stand up for it. Law, policy, governance, and human rights work are calling your name."],
    ];

    /** Fallback track set — the groupings courses/jobs are filed under. */
    const DEFAULT_TRACKS = [
        ['key' => 'tech',     'label' => 'Technology', 'icon' => '💻', 'color' => '#4DA8F7'],
        ['key' => 'business', 'label' => 'Business',   'icon' => '💼', 'color' => '#A78BFA'],
        ['key' => 'finance',  'label' => 'Finance',    'icon' => '📊', 'color' => '#15C77E'],
        ['key' => 'creative', 'label' => 'Creative',   'icon' => '🎨', 'color' => '#FF6B35'],
    ];

    private static ?array $fieldsCache = null;
    private static ?array $tracksCache = null;

    /** All career fields (quiz interest categories), admin-configured or defaults. */
    public static function fields(): array
    {
        if (self::$fieldsCache !== null) return self::$fieldsCache;

        $saved = [];
        try {
            $saved = json_decode(Setting::get('career_fields', '') ?: '[]', true) ?: [];
        } catch (\Throwable $e) {
            // settings table unavailable (fresh install) — use defaults
        }

        return self::$fieldsCache = !empty($saved) ? $saved : self::DEFAULT_FIELDS;
    }

    /** All career tracks (course/job groupings), admin-configured or defaults. */
    public static function tracks(): array
    {
        if (self::$tracksCache !== null) return self::$tracksCache;

        $saved = [];
        try {
            $saved = json_decode(Setting::get('career_tracks', '') ?: '[]', true) ?: [];
        } catch (\Throwable $e) {
            // settings table unavailable (fresh install) — use defaults
        }

        return self::$tracksCache = !empty($saved) ? $saved : self::DEFAULT_TRACKS;
    }

    /** key => full field row map, for quick lookups. */
    public static function fieldsByKey(): array
    {
        return collect(self::fields())->keyBy('key')->all();
    }

    /** key => full track row map, for quick lookups. */
    public static function tracksByKey(): array
    {
        return collect(self::tracks())->keyBy('key')->all();
    }

    /** The track a given career field recommends (for course/job matching). */
    public static function trackForField(?string $field): ?string
    {
        if (!$field) return null;
        return self::fieldsByKey()[$field]['track'] ?? null;
    }

    public function isDueIncome(UserProgress $progress): bool
    {
        if ($progress->career_income_rate <= 0) return false;
        if (!$progress->career_income_claimed_at) return true;
        // Carbon 3 returns a float — floor to whole elapsed days
        return (int) floor($progress->career_income_claimed_at->diffInDays(now())) >= 30;
    }

    public function generatePayslip(UserProgress $progress): array
    {
        $gross = $progress->career_income_rate;
        if ($gross <= 0) return [];

        $paye = $this->calculatePAYE($gross);
        $nhif = $this->calculateNHIF($gross);
        $nssf = min((int) round($gross * 0.06), 2160);
        $loans = (int) collect($progress->active_loans ?? [])->sum('monthly_payment');
        $net  = max(0, $gross - $paye - $nhif - $nssf - $loans);

        return compact('gross', 'paye', 'nhif', 'nssf', 'loans', 'net');
    }

    public function claimIncome(UserProgress $progress): array
    {
        $slip = $this->generatePayslip($progress);
        if (empty($slip)) return [];

        // Low mood (< 40) reduces work performance — 10% net income penalty
        if (($progress->mood ?? 70) < 40) {
            $slip['mood_penalty'] = (int) round($slip['net'] * 0.1);
            $slip['net']          = $slip['net'] - $slip['mood_penalty'];
        }

        $progress->balance += $slip['net'];
        $progress->career_income_claimed_at = now();
        $progress->save();
        return $slip;
    }

    public function fieldMeta(string $field): array
    {
        $meta = self::fieldsByKey()[$field] ?? ['label' => ucfirst($field), 'icon' => '💼', 'color' => '#6b7280'];
        $meta['desc'] = $meta['desc'] ?? "Your answers point toward {$meta['label']} — a field where your interests can grow into a real career.";
        return $meta;
    }

    private function calculatePAYE(int $gross): int
    {
        $personalRelief = 2400;
        if ($gross <= 24000)      $tax = $gross * 0.10;
        elseif ($gross <= 32333)  $tax = 2400 + ($gross - 24000) * 0.25;
        else                      $tax = 2400 + 2083.25 + ($gross - 32333) * 0.30;
        return max(0, (int) round($tax - $personalRelief));
    }

    private function calculateNHIF(int $gross): int
    {
        $bands = [
            5999=>150,7999=>300,11999=>400,14999=>500,19999=>600,
            24999=>750,29999=>850,34999=>900,39999=>950,44999=>1000,
            49999=>1100,59999=>1200,69999=>1300,79999=>1400,89999=>1500,99999=>1600,
        ];
        foreach ($bands as $limit => $amount) {
            if ($gross <= $limit) return $amount;
        }
        return 1700;
    }
}
