<?php

namespace App\Services;

/**
 * Composes quest/contract copy from config/pesa_voice.php.
 * NPC (greeting/signoff) × archetype (title/pitch/label/lesson) factorization
 * means a few hundred written lines yield thousands of distinct combinations —
 * randomized per call so contracts never read as template output.
 */
class PesaVoice
{
    /** Resolve an age_group ('8-12'|'13-17'|'18-25'|'26+'|null) to a copy band. */
    public static function band(?string $ageGroup): string
    {
        return config('pesa_voice.bands')[$ageGroup] ?? 'adult';
    }

    /** All archetype keys. */
    public static function archetypes(): array
    {
        return array_keys(config('pesa_voice.archetypes', []));
    }

    public static function archetype(string $key): ?array
    {
        return config("pesa_voice.archetypes.{$key}");
    }

    /** Pick an NPC who "owns" this archetype (any NPC as fallback). */
    public static function npcFor(string $archetype): array
    {
        $npcs = config('pesa_voice.npcs', []);
        $owners = array_filter($npcs, fn ($n) => in_array($archetype, $n['domains'] ?? [], true));
        $pool = $owners ?: $npcs;
        $key = array_rand($pool);

        return ['key' => $key] + $pool[$key];
    }

    /**
     * Compose a full copy set for one archetype.
     * $vars: name, n, amount, days, course, job, employer, chama…
     * $targeted: use the factory's specific-content pitch/label variants.
     */
    public static function compose(string $archetypeKey, ?string $ageGroup, array $vars = [], bool $targeted = false): ?array
    {
        $arch = self::archetype($archetypeKey);
        if (!$arch) return null;

        $band = self::band($ageGroup);
        $npc  = self::npcFor($archetypeKey);

        $pitches = ($targeted && isset($arch['targeted_pitches']))
            ? ($arch['targeted_pitches'][$band] ?? reset($arch['targeted_pitches']))
            : ($arch['pitches'][$band] ?? reset($arch['pitches']));
        $labels = ($targeted && isset($arch['targeted_labels']))
            ? $arch['targeted_labels']
            : $arch['labels'];

        $greeting = self::pick($npc['greetings'][$band] ?? reset($npc['greetings']));
        $signoff  = self::pick($npc['signoffs'][$band] ?? reset($npc['signoffs']));
        $title    = self::pick($arch['titles'][$band] ?? reset($arch['titles']));
        $pitch    = self::pick($pitches);
        $label    = self::pick($labels);
        $lesson   = self::pick($arch['lessons'][$band] ?? reset($arch['lessons']));

        return [
            'npc_key'   => $npc['key'],
            'npc_name'  => $npc['name'],
            'npc_emoji' => $npc['emoji'],
            'icon'      => $arch['icon'] ?? '🎯',
            'metric'    => $arch['metric'] ?? null,
            'title'     => self::fill($title, $vars),
            'intro'     => self::fill($greeting . ' ' . $pitch, $vars),
            'label'     => self::fill($label, $vars),
            'lesson'    => self::fill($lesson, $vars),
            'signoff'   => self::fill($signoff, $vars),
        ];
    }

    /** Just the objective label for an archetype (used for extra objectives on one contract). */
    public static function objectiveLabel(string $archetypeKey, array $vars = []): string
    {
        $arch = self::archetype($archetypeKey);
        return $arch ? self::fill(self::pick($arch['labels']), $vars) : 'Complete the task';
    }

    /** Replace {placeholders}, including plural helpers {s} and {ies} driven by $vars['n']. */
    public static function fill(string $template, array $vars): string
    {
        $n = (int) ($vars['n'] ?? 1);
        $vars['s']   = $n === 1 ? '' : 's';
        $vars['ies'] = $n === 1 ? 'y' : 'ies';
        if (isset($vars['amount']) && is_numeric($vars['amount'])) {
            $vars['amount'] = number_format((float) $vars['amount']);
        }

        return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($vars) {
            return array_key_exists($m[1], $vars) ? (string) $vars[$m[1]] : $m[0];
        }, $template);
    }

    private static function pick(array $options): string
    {
        return $options[array_rand($options)];
    }
}
