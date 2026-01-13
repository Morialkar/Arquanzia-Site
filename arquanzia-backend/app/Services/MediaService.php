<?php

namespace App\Services;

use App\Models\PostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function store(UploadedFile $file, string $postId, int $position): PostMedia
    {
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $mime = $file->getMimeType();

        $originalPath = "media/original/{$uuid}.{$extension}";
        $lockedPath = "media/locked/{$uuid}.{$extension}";

        Storage::disk('local')->put($originalPath, file_get_contents($file->getRealPath()));

        $image = $this->imageManager->read($file->getRealPath());
        $originalWidth = $image->width();
        $originalHeight = $image->height();

        $lockedImage = $this->generateLockedVersion($file->getRealPath());
        Storage::disk('local')->put($lockedPath, $lockedImage['data']);

        return PostMedia::create([
            'id' => $uuid,
            'post_id' => $postId,
            'position' => $position,
            'original_path' => $originalPath,
            'locked_path' => $lockedPath,
            'mime' => $mime,
            'original_width' => $originalWidth,
            'original_height' => $originalHeight,
            'locked_width' => $lockedImage['width'],
            'locked_height' => $lockedImage['height'],
        ]);
    }

    protected function generateLockedVersion(string $path): array
    {
        $image = $this->imageManager->read($path);

        $targetWidth = min(400, $image->width());
        $scale = $targetWidth / $image->width();
        $targetHeight = (int) ($image->height() * $scale);

        $image->resize($targetWidth, $targetHeight);

        $image->blur(50);

        $pixelSize = max(8, (int) ($targetWidth / 25));
        $image->pixelate($pixelSize);

        $image->blur(30);

        $encoded = $image->toJpeg(60);

        return [
            'data' => (string) $encoded,
            'width' => $targetWidth,
            'height' => $targetHeight,
        ];
    }

    public function getPath(PostMedia $media, bool $unlocked): string
    {
        return $unlocked ? $media->original_path : $media->locked_path;
    }

    public function getMimeType(PostMedia $media): string
    {
        return $media->mime;
    }

    public function delete(PostMedia $media): void
    {
        Storage::disk('local')->delete($media->original_path);
        Storage::disk('local')->delete($media->locked_path);
        $media->delete();
    }
}
