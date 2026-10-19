<?php

namespace App\Services;

use App\Models\Hotel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Stores hotel images under public/assets/img and keeps an uploaded image
 * between requests (validation error / back from the confirm screen),
 * because browsers cannot refill a file input.
 */
class HotelImageService
{
    /** directory (under public/assets/img) for images not saved to a hotel yet */
    public const TMP_DIR = Hotel::IMAGE_DIR . '/tmp';

    /** session key of the image kept for the form */
    private const KEPT_KEY = 'hotel_form_image';

    /**
     * @return string path relative to public/assets/img
     */
    public function store(UploadedFile $file, string $dir): string
    {
        $fileName = Str::random(40) . '.' . $file->extension();
        $file->move($this->fullPath($dir), $fileName);

        return $dir . '/' . $fileName;
    }

    /**
     * Move a temporary image to the hotel image directory
     *
     * @return string new path relative to public/assets/img
     */
    public function publish(string $tmpPath): string
    {
        $newPath = Hotel::IMAGE_DIR . '/' . basename($tmpPath);
        File::move($this->fullPath($tmpPath), $this->fullPath($newPath));

        return $newPath;
    }

    public function delete(?string $relativePath): void
    {
        if ($relativePath !== null) {
            File::delete($this->fullPath($relativePath));
        }
    }

    /**
     * Keep an image for the given form (e.g. "create", "edit:12"), replacing the previous one
     */
    public function keep(string $form, string $tmpPath): void
    {
        $current = session(self::KEPT_KEY);
        if (is_array($current) && $current['path'] !== $tmpPath) {
            $this->delete($current['path']);
        }
        session([self::KEPT_KEY => ['form' => $form, 'path' => $tmpPath]]);
    }

    /**
     * Kept image of the given form, or null
     */
    public function kept(string $form): ?string
    {
        $kept = session(self::KEPT_KEY);
        if (!is_array($kept) || $kept['form'] !== $form || !File::exists($this->fullPath($kept['path']))) {
            return null;
        }

        return $kept['path'];
    }

    /**
     * Stop keeping the image; the file is deleted unless it was taken over by the caller
     */
    public function forget(bool $deleteFile = true): void
    {
        $kept = session()->pull(self::KEPT_KEY);
        if ($deleteFile && is_array($kept)) {
            $this->delete($kept['path']);
        }
    }

    public function fullPath(string $relativePath): string
    {
        return public_path('assets/img/' . $relativePath);
    }
}
