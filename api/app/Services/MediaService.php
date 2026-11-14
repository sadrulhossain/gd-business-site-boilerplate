<?php

namespace App\Services;

use App\Jobs\GenerateWebpVariants;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Stores uploads on the "uploads" disk as `<dir>/<random>/original.<ext>`;
 * WebP variants (`<width>.webp`) land in the same folder via a queued job.
 * The stored path is what models keep in their `image` column.
 */
class MediaService
{
    /**
     * FormRequest rules for an image upload field.
     *
     * @return list<string>
     */
    public static function imageRules(): array
    {
        return [
            'file',
            'mimetypes:'.implode(',', array_keys(config('uploads.mimes'))),
            'max:'.(config('uploads.max_mb') * 1024),
            'dimensions:max_width=6000,max_height=6000',
        ];
    }

    /** Icon files (favicon only): not images GD can resize, so they are stored as they are, without variants. */
    public const ICON_MIMES = ['image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico'];

    /**
     * FormRequest rules for the favicon: the usual images plus .ico.
     *
     * @return list<string>
     */
    public static function faviconRules(): array
    {
        return [
            'file',
            'mimetypes:'.implode(',', [...array_keys(config('uploads.mimes')), ...array_keys(self::ICON_MIMES)]),
            'max:'.(config('uploads.max_mb') * 1024),
        ];
    }

    /** Stores the file under a random name (extension from its content type) and queues the variants. */
    public function store(UploadedFile $file, string $dir, bool $allowIcon = false): string
    {
        $extension = (config('uploads.mimes') + ($allowIcon ? self::ICON_MIMES : []))[$file->getMimeType()] ?? null;

        if ($extension === null) {
            throw new InvalidArgumentException('Unsupported media type.');
        }

        $folder = $dir.'/'.Str::lower(Str::random(32));
        $path = "{$folder}/original.{$extension}";

        $this->disk()->putFileAs($folder, $file, "original.{$extension}");

        if ($extension !== 'ico') {
            GenerateWebpVariants::dispatch($path);
        }

        return $path;
    }

    /** Removes the original and all variants. */
    public function delete(?string $path): void
    {
        if ($path !== null && dirname($path) !== '.') {
            $this->disk()->deleteDirectory(dirname($path));
        }
    }

    /**
     * URLs for API responses; variants appear once the queue has produced them.
     *
     * @return array{url: string, webp: object}|null
     */
    public function describe(?string $path): ?array
    {
        if ($path === null) {
            return null;
        }

        $variants = collect($this->disk()->files(dirname($path)))
            ->filter(fn (string $file) => str_ends_with($file, '.webp'))
            ->mapWithKeys(fn (string $file) => [(int) basename($file, '.webp') => $this->disk()->url($file)])
            ->sortKeys();

        return ['url' => $this->disk()->url($path), 'webp' => (object) $variants->all()];
    }

    private function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter */
        return Storage::disk('uploads');
    }
}
