<?php

namespace App\Support;

class ReaderPreferences
{
    public const MIN_PERCENT = 85;
    public const MAX_PERCENT = 130;
    public const BASE_FONT_SIZE_PX = 18;

    public static function clampPercent(int $value): int
    {
        return max(self::MIN_PERCENT, min(self::MAX_PERCENT, $value));
    }

    public static function percentToStored(int $percent): int
    {
        $percent = self::clampPercent($percent);
        return (int) round(self::BASE_FONT_SIZE_PX * ($percent / 100));
    }

    public static function storedToPercent(?int $stored): int
    {
        if (!$stored || $stored <= 0) {
            $stored = self::BASE_FONT_SIZE_PX;
        }

        $percent = (int) round(($stored / self::BASE_FONT_SIZE_PX) * 100);
        return self::clampPercent($percent);
    }

    public static function normalizeFont(?string $font): string
    {
        return $font === 'dyslexic' ? 'dyslexic' : 'standard';
    }
}
