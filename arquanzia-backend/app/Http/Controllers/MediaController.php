<?php

namespace App\Http\Controllers;

use App\Models\PostMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function show(Request $request, PostMedia $media): StreamedResponse
    {
        $path = $media->filename
            ? 'media/original/' . $media->filename
            : $media->original_path;

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $headers = [
            'Content-Type' => $media->mime,
            'Cache-Control' => 'public, max-age=31536000',
        ];

        return Storage::disk('local')->response($path, null, $headers);
    }
}
