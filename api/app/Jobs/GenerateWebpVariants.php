<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/** Writes `<dir>/<width>.webp` next to the original for each configured width. */
class GenerateWebpVariants implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public string $path) {}

    public function handle(): void
    {
        $disk = Storage::disk('uploads');

        // Replaced or deleted before the job ran.
        if (! $disk->exists($this->path)) {
            return;
        }

        $bytes = (string) $disk->get($this->path);
        $info = getimagesizefromstring($bytes);

        if ($info === false || config('uploads.max_pixels') < $info[0] * $info[1]) {
            return;
        }

        $source = imagecreatefromstring($bytes);

        if ($source === false) {
            return;
        }

        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);

        $full = $info[0];
        /** @var list<int> $configured */
        $configured = config('uploads.variant_widths');
        $widths = collect($configured)
            ->filter(fn (int $width) => $width < $full)
            ->push($full)
            ->unique()
            ->sort();

        foreach ($widths as $width) {
            $image = $width === $full ? $source : imagescale($source, $width, -1, IMG_BICUBIC);

            if ($image === false) {
                continue;
            }

            ob_start();
            imagewebp($image, null, (int) config('uploads.webp_quality'));
            $disk->put(dirname($this->path)."/{$width}.webp", (string) ob_get_clean());
        }
    }
}
