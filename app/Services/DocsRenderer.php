<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * Renders one of the docs/*.md guides into HTML for an in-app documentation
 * page, injecting anchor ids on ## / ### headings so a sidebar table of
 * contents can jump to them (Str::markdown's default CommonMark config
 * doesn't add heading ids on its own).
 */
class DocsRenderer
{
    public static function render(string $relativePath): array
    {
        $path = base_path($relativePath);
        if (!is_file($path)) {
            return ['html' => '<p>Guide not found.</p>', 'toc' => []];
        }

        $html = Str::markdown(file_get_contents($path), [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $toc  = [];
        $seen = [];
        $html = preg_replace_callback(
            '/<h([23])>(.*?)<\/h\1>/s',
            function ($m) use (&$toc, &$seen) {
                [, $level, $text] = $m;
                $plain = trim(html_entity_decode(strip_tags($text)));
                $slug  = Str::slug($plain) ?: 'section';
                $base  = $slug;
                $i     = 2;
                while (isset($seen[$slug])) {
                    $slug = $base . '-' . $i++;
                }
                $seen[$slug] = true;

                if ($level === '2' && strcasecmp($plain, 'Table of Contents') !== 0) {
                    $toc[] = ['slug' => $slug, 'text' => $plain];
                }

                return '<h' . $level . ' id="' . $slug . '">' . $text . '</h' . $level . '>';
            },
            $html
        );

        return ['html' => $html, 'toc' => $toc];
    }
}
