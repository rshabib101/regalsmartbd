<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ImageOptimizerService
{
    /**
     * Upload and optimize an image file.
     * Converts to WebP, limits max dimensions to 1200px, and compresses to ~82% quality.
     *
     * @param UploadedFile $file
     * @param string $destinationDir (e.g. 'public/uploads/product/')
     * @param string $prefix
     * @return string Relative path of the saved image (e.g. 'public/uploads/product/1712345678-abc.webp')
     */
    public static function uploadProductImage(UploadedFile $file, string $destinationDir = 'public/uploads/product/', string $prefix = ''): string
    {
        $destinationDir = rtrim($destinationDir, '/') . '/';
        $fullDestinationPath = base_path($destinationDir);

        if (!File::isDirectory($fullDestinationPath)) {
            File::makeDirectory($fullDestinationPath, 0775, true, true);
        }

        // Clean sanitized base filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '-', $originalName));
        $cleanName = substr($cleanName, 0, 40);

        $uniqueId = uniqid();
        $timestamp = time();
        $filename = ($prefix ? $prefix . '-' : '') . $timestamp . '-' . $uniqueId . ($cleanName ? '-' . $cleanName : '') . '.webp';
        $savePath = $fullDestinationPath . $filename;

        try {
            // Read image with Intervention Image
            $img = Image::make($file->getRealPath());

            // Orientate image based on EXIF data if present
            $img->orientate();

            // Resize down if larger than 1200x1200px, maintaining aspect ratio without upscaling
            if ($img->width() > 1200 || $img->height() > 1200) {
                $img->resize(1200, 1200, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Encode to WebP format with 82 quality (great balance of crystal clear quality and ~60-100KB size)
            $img->encode('webp', 82)->save($savePath);

            return $destinationDir . $filename;
        } catch (\Throwable $e) {
            // Fallback to standard move if GD/Intervention fails for any reason
            $fallbackExt = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $fallbackFilename = ($prefix ? $prefix . '-' : '') . $timestamp . '-' . $uniqueId . '.' . $fallbackExt;
            $file->move($fullDestinationPath, $fallbackFilename);

            return $destinationDir . $fallbackFilename;
        }
    }
}
