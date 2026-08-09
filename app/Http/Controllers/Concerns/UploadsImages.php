<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Resizes + compresses an uploaded image directly into public/uploads/ (no
 * storage symlink needed — works on any host). Shared by any controller that
 * accepts user-uploaded images (profile photos, forum post/reply images).
 */
trait UploadsImages
{
    protected function resizeAndStore($uploadedFile, string $folder, int $targetW, int $targetH, int $quality = 82): string
    {
        $mime    = $uploadedFile->getMimeType();
        $tmpPath = $uploadedFile->getRealPath();

        $src = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($tmpPath),
            'image/png'  => imagecreatefrompng($tmpPath),
            'image/gif'  => imagecreatefromgif($tmpPath),
            'image/webp' => imagecreatefromwebp($tmpPath),
            default      => imagecreatefromjpeg($tmpPath),
        };

        [$srcW, $srcH] = getimagesize($tmpPath);

        // Cover-fit: crop to target ratio then scale
        $srcRatio = $srcW / $srcH;
        $tgtRatio = $targetW / $targetH;

        if ($srcRatio > $tgtRatio) {
            $cropH = $srcH;
            $cropW = (int) ($srcH * $tgtRatio);
            $cropX = (int) (($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            $cropW = $srcW;
            $cropH = (int) ($srcW / $tgtRatio);
            $cropX = 0;
            $cropY = (int) (($srcH - $cropH) / 2);
        }

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

        $filename = $folder . '/' . uniqid() . '.jpg';
        $fullPath = public_path('uploads/' . $filename);

        @mkdir(dirname($fullPath), 0755, true);
        imagejpeg($dst, $fullPath, $quality);

        imagedestroy($src);
        imagedestroy($dst);

        return $filename;
    }

    /** Deletes a stored /uploads/... image (and legacy /storage/... if ever passed one). */
    protected function deleteStoredImage(?string $path): void
    {
        if (!$path) return;

        if (str_starts_with($path, '/uploads/')) {
            @unlink(public_path(ltrim($path, '/')));
            return;
        }

        if (str_starts_with($path, '/storage/')) {
            Storage::disk('public')->delete(preg_replace('#^/storage/#', '', $path));
        }
    }
}
