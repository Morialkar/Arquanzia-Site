<?php

namespace App\Http\Controllers;

use App\Services\ViewerResolver;
use App\Support\ReaderPreferences;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReaderPreferenceController extends Controller
{
    public function __construct(private ViewerResolver $viewerResolver)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $context = $this->viewerResolver->resolve($request);

        if (!$context['is_logged_in']) {
            return response()->json(['error' => 'Non connecté'], 401);
        }

        $validated = $request->validate([
            'font_size_percent' => 'nullable|integer|min:' . ReaderPreferences::MIN_PERCENT . '|max:' . ReaderPreferences::MAX_PERCENT,
            'font' => 'nullable|in:standard,dyslexic',
        ]);

        $updates = [];

        if (array_key_exists('font_size_percent', $validated)) {
            $updates['reader_font_size'] = ReaderPreferences::percentToStored($validated['font_size_percent']);
        }

        if (array_key_exists('font', $validated)) {
            $updates['reader_font'] = ReaderPreferences::normalizeFont($validated['font']);
        }

        if (!empty($updates)) {
            $context['user']->update($updates);
        }

        return response()->json([
            'success' => true,
            'font_size_percent' => ReaderPreferences::storedToPercent($context['user']->reader_font_size ?? null),
            'font' => ReaderPreferences::normalizeFont($context['user']->reader_font ?? null),
        ]);
    }
}
