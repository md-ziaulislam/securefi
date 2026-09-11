<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get all theme tokens formatted as an associative array.
     */
    public static function getAllTokens(): array
    {
        return Cache::rememberForever('theme_settings_tokens', function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, $default = null)
    {
        $tokens = static::getAllTokens();
        return $tokens[$key] ?? $default;
    }

    public static function set(string $key, $value, string $type = 'color', ?string $description = null)
    {
        Cache::forget('theme_settings_tokens');
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'description' => $description]
        );
    }
}
