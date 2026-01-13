<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): mixed
    {
        return Cache::remember("site_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget("site_setting_{$key}");
    }

    public static function getLogo(): ?string
    {
        return static::get('site_logo');
    }

    public static function getLogoVersion(): int
    {
        $setting = static::where('key', 'site_logo')->first();
        return $setting ? $setting->updated_at->timestamp : 0;
    }

    public static function getSiteName(): string
    {
        return static::get('site_name', 'Arquanzia');
    }
}
