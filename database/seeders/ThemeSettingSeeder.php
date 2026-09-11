<?php

namespace Database\Seeders;

use App\Models\ThemeSetting;
use Illuminate\Database\Seeder;

class ThemeSettingSeeder extends Seeder
{
    public function run(): void
    {
        $tokens = [
            // Colors matching design.md
            ['key' => 'color_primary', 'value' => '#111111', 'type' => 'color', 'description' => 'Primary Brand & Headings (Off-Black/Charcoal)'],
            ['key' => 'color_secondary', 'value' => '#FFFFFF', 'type' => 'color', 'description' => 'Light Surface & Card Background'],
            ['key' => 'color_tertiary', 'value' => '#F5F1E8', 'type' => 'color', 'description' => 'Decorative Background (Warm Beige)'],
            ['key' => 'color_neutral', 'value' => '#808080', 'type' => 'color', 'description' => 'Muted Text & Border Stroke (Grey)'],
            ['key' => 'color_surface', 'value' => '#B38B6D', 'type' => 'color', 'description' => 'Decorative Accent (Taupe)'],

            // Typography
            ['key' => 'font_heading', 'value' => 'Inter', 'type' => 'font', 'description' => 'Heading Font Family'],
            ['key' => 'font_body', 'value' => 'Inter', 'type' => 'font', 'description' => 'Body Font Family'],
            ['key' => 'font_mono', 'value' => 'JetBrains Mono', 'type' => 'font', 'description' => 'Code and Metadata Font'],

            // Geometry & Shapes (design.md: 0px base, sm: 2px, md: 4px, lg: 8px)
            ['key' => 'radius_base', 'value' => '0px', 'type' => 'dimension', 'description' => 'Base Corner Radius (Sharp Swiss Style)'],
            ['key' => 'radius_sm', 'value' => '2px', 'type' => 'dimension', 'description' => 'Small Corner Radius'],
            ['key' => 'radius_md', 'value' => '4px', 'type' => 'dimension', 'description' => 'Medium Corner Radius'],
            ['key' => 'radius_lg', 'value' => '8px', 'type' => 'dimension', 'description' => 'Large Corner Radius'],

            // Density & Mode
            ['key' => 'spacing_density', 'value' => 'airy', 'type' => 'select', 'description' => 'Layout Spacing Density (compact, balanced, airy)'],
            ['key' => 'default_theme_mode', 'value' => 'light', 'type' => 'select', 'description' => 'Default Site Theme Mode (light, dark, system)'],
            ['key' => 'button_style', 'value' => 'sharp_solid', 'type' => 'select', 'description' => 'Primary Button Appearance'],
        ];

        foreach ($tokens as $token) {
            ThemeSetting::updateOrCreate(['key' => $token['key']], $token);
        }
    }
}
