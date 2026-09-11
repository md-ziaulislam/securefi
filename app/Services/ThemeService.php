<?php

namespace App\Services;

use App\Models\ThemeSetting;
use Illuminate\Support\HtmlString;

class ThemeService
{
    /**
     * Generate inline CSS custom properties based on database theme tokens.
     */
    public static function renderCssVariables(): HtmlString
    {
        $tokens = ThemeSetting::getAllTokens();

        $primary = $tokens['color_primary'] ?? '#111111';
        $secondary = $tokens['color_secondary'] ?? '#FFFFFF';
        $tertiary = $tokens['color_tertiary'] ?? '#F5F1E8';
        $neutral = $tokens['color_neutral'] ?? '#808080';
        $surface = $tokens['color_surface'] ?? '#B38B6D';

        $fontHeading = $tokens['font_heading'] ?? 'Inter';
        $fontBody = $tokens['font_body'] ?? 'Inter';
        $fontMono = $tokens['font_mono'] ?? 'JetBrains Mono';

        $radiusBase = $tokens['radius_base'] ?? '0px';
        $radiusSm = $tokens['radius_sm'] ?? '2px';
        $radiusMd = $tokens['radius_md'] ?? '4px';
        $radiusLg = $tokens['radius_lg'] ?? '8px';

        $css = "
        :root {
            --color-primary: {$primary};
            --color-secondary: {$secondary};
            --color-tertiary: {$tertiary};
            --color-neutral: {$neutral};
            --color-surface: {$surface};

            --font-heading: '{$fontHeading}', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-body: '{$fontBody}', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-mono: '{$fontMono}', monospace;

            --radius-base: {$radiusBase};
            --radius-sm: {$radiusSm};
            --radius-md: {$radiusMd};
            --radius-lg: {$radiusLg};
        }
        ";

        return new HtmlString("<style id=\"securofi-theme-vars\">{$css}</style>");
    }
}
