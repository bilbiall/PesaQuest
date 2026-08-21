<?php

namespace App\Console\Commands;

use App\Models\Quest;
use Illuminate\Console\Command;

class RenumberQuestSortOrder extends Command
{
    protected $signature   = 'quests:renumber-sort-order {--dry-run : Report only, make no changes}';
    protected $description = 'Reset quest sort_order to a small 0-based sequence within each level_required + age_group bucket (the scope players actually see)';

    public function handle(): int
    {
        $dryRun  = $this->option('dry-run');
        $touched = 0;

        Quest::query()
            ->select('id', 'level_required', 'age_group', 'sort_order')
            ->orderBy('level_required')
            ->orderBy('age_group')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Quest $q) => ($q->level_required ?? 1) . '|' . ($q->age_group ?? 'all'))
            ->each(function ($bucket) use (&$touched, $dryRun) {
                foreach ($bucket->values() as $i => $quest) {
                    if ($quest->sort_order === $i) continue;
                    $touched++;
                    if (!$dryRun) {
                        Quest::where('id', $quest->id)->update(['sort_order' => $i]);
                    }
                }
            });

        $this->info(($dryRun ? '[dry-run] ' : '') . "{$touched} quest(s) renumbered to a per-level/age-group sort_order.");

        return 0;
    }
}
