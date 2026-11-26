<?php

namespace App\Actions;

use App\Models\ProjectImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DeleteProjectImageFiles implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @param array<string, list<string>> $pathsByDisk */
    public function __construct(private array $pathsByDisk) {}

    /** @param Collection<int, ProjectImage> $images */
    public static function afterDeletion(Collection $images): void
    {
        if ($images->isEmpty()) {
            return;
        }

        $pathsByDisk = [];
        foreach ($images as $image) {
            $pathsByDisk[$image->disk][] = $image->image_path;
        }

        DB::afterCommit(function () use ($pathsByDisk): void {
            try {
                (new self($pathsByDisk))->handle();
            } catch (Throwable $exception) {
                report($exception);
                self::dispatch($pathsByDisk)->onConnection('database');
            }
        });
    }

    public function handle(): void
    {
        foreach ($this->pathsByDisk as $disk => $paths) {
            if (! Storage::disk($disk)->delete($paths)) {
                throw new RuntimeException("Failed to remove project images from the {$disk} disk.");
            }
        }
    }
}
