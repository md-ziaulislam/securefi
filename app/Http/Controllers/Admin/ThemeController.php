<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeController extends Controller
{
    public function index()
    {
        $tokens = ThemeSetting::getAllTokens();

        $fonts = [
            'Inter' => 'Inter (Modern Sans-serif)',
            'Roboto' => 'Roboto (Clean Geometric)',
            'Outfit' => 'Outfit (Contemporary Tech)',
            'JetBrains Mono' => 'JetBrains Mono (Technical Monospace)',
            'System' => '-apple-system, BlinkMacSystemFont, Segoe UI',
        ];

        return view('admin.theme.index', compact('tokens', 'fonts'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'color_primary' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'color_secondary' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'color_tertiary' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'color_neutral' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'color_surface' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'font_heading' => 'required|string|max:100',
            'font_body' => 'required|string|max:100',
            'font_mono' => 'required|string|max:100',
            'radius_base' => 'required|string|max:20',
            'radius_sm' => 'required|string|max:20',
            'radius_md' => 'required|string|max:20',
            'radius_lg' => 'required|string|max:20',
            'theme_density' => 'nullable|string|in:compact,balanced,airy',
            'button_style' => 'nullable|string|in:sharp,rounded',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $type = str_starts_with($key, 'color_') ? 'color' : (str_starts_with($key, 'font_') ? 'font' : 'token');
                ThemeSetting::set($key, $value, $type);
            }
        }

        Cache::forget('theme_settings_tokens');

        return back()->with('success', 'Design tokens successfully compiled to CSS variables.');
    }

    public function reset()
    {
        $defaults = [
            'color_primary' => '#111111',
            'color_secondary' => '#FFFFFF',
            'color_tertiary' => '#F5F1E8',
            'color_neutral' => '#808080',
            'color_surface' => '#B38B6D',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
            'font_mono' => 'JetBrains Mono',
            'radius_base' => '0px',
            'radius_sm' => '2px',
            'radius_md' => '4px',
            'radius_lg' => '8px',
            'theme_density' => 'balanced',
            'button_style' => 'sharp',
        ];

        foreach ($defaults as $key => $val) {
            $type = str_starts_with($key, 'color_') ? 'color' : (str_starts_with($key, 'font_') ? 'font' : 'token');
            ThemeSetting::set($key, $val, $type);
        }

        Cache::forget('theme_settings_tokens');

        return back()->with('success', 'Theme reset to default Swiss Minimalist tokens.');
    }
}
