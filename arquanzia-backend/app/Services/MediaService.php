<?php

namespace App\Services;

use App\Models\PostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver);
    }

    public function store(UploadedFile $file, string $postId, int $position): PostMedia
    {
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $mime = $file->getMimeType();

        $originalPath = "media/original/{$uuid}.{$extension}";

        Storage::disk('local')->put($originalPath, file_get_contents($file->getRealPath()));

        $image = $this->imageManager->read($file->getRealPath());

        return PostMedia::create([
            'id' => $uuid,
            'post_id' => $postId,
            'position' => $position,
            'original_path' => $originalPath,
            'locked_path' => $originalPath,
            'mime' => $mime,
            'original_width' => $image->width(),
            'original_height' => $image->height(),
            'locked_width' => $image->width(),
            'locked_height' => $image->height(),
        ]);
    }

    public function delete(PostMedia $media): void
    {
        Storage::disk('local')->delete($media->original_path);
        $media->delete();
    }
}
