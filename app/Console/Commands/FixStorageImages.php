<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FixStorageImages extends Command
{
    protected $signature   = 'app:fix-storage-images {--dry-run : Report only, make no changes}';
    protected $description = 'Migrate profile images from /storage/ to /uploads/ and fix missing files';

    public function handle(): int
    {
        $dryRun  = $this->option('dry-run');
        $lines   = [];
        $fixed   = 0;
        $missing = 0;
        $ok      = 0;

        // Ensure uploads directories exist
        if (!$dryRun) {
            @mkdir(public_path('uploads/profiles/avatar'), 0755, true);
            @mkdir(public_path('uploads/profiles/cover'),  0755, true);
        }

        $users = DB::table('users')
            ->where(function ($q) {
                $q->whereNotNull('profile_photo')
                  ->orWhereNotNull('cover_photo');
            })
            ->get(['id', 'name', 'profile_photo', 'cover_photo']);

        foreach ($users as $u) {
            $updates = [];

            foreach (['profile_photo' => 'profiles/avatar', 'cover_photo' => 'profiles/cover'] as $field => $folder) {
                $url = $u->$field ?? null;
                if (!$url) continue;

                // Already on new /uploads/ path — verify file exists
                if (str_contains($url, '/uploads/')) {
                    $rel  = ltrim(preg_replace('#^https?://[^/]+#', '', $url), '/');
                    $full = public_path($rel);
                    if (file_exists($full)) {
                        $ok++;
                    } else {
                        $missing++;
                        $lines[] = "  ✘ Missing: user #{$u->id} {$field} → {$url}";
                    }
                    continue;
                }

                // Old /storage/ path — try to migrate to /uploads/
                $rel = ltrim(preg_replace('#^https?://[^/]+#', '', $url), '/');
                $rel = preg_replace('#^storage/#', '', $rel);  // → profiles/avatar/xxx.jpg

                $srcPath  = storage_path('app/public/' . $rel);
                $destRel  = $rel;                              // same relative path under uploads/
                $destFull = public_path('uploads/' . $destRel);

                if (file_exists($srcPath)) {
                    if (!$dryRun) {
                        @mkdir(dirname($destFull), 0755, true);
                        copy($srcPath, $destFull);
                    }
                    $updates[$field] = '/uploads/' . $destRel;
                    $fixed++;
                    $lines[] = "  ✔ Migrated: user #{$u->id} {$field} → /uploads/{$destRel}";
                } else {
                    // File genuinely missing from storage too
                    $missing++;
                    $lines[] = "  ✘ Missing on disk: user #{$u->id} {$field} → {$url}";
                }
            }

            if (!empty($updates) && !$dryRun) {
                DB::table('users')->where('id', $u->id)->update($updates);
            }
        }

        $this->line(implode("\n", $lines) ?: '  (no legacy /storage/ paths found)');
        $this->line('');
        $this->info("Profile images: {$ok} already on /uploads/, {$fixed} migrated, {$missing} missing from disk.");

        if ($dryRun) {
            $this->warn('Dry run — no changes made. Remove --dry-run to apply.');
        } elseif ($fixed > 0) {
            $this->info("{$fixed} image(s) copied to public/uploads/ and DB records updated.");
            $this->info('Old files left in storage/app/public/ (safe to delete once you verify).');
        }

        return 0;
    }
}
