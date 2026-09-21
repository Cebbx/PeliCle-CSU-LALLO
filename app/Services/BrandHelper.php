<?php

namespace App\Services;

class BrandHelper
{
    private static ?string $base64Logo = null;

    public static function getLogoDataUri(): string
    {
        if (self::$base64Logo !== null) {
            return self::$base64Logo;
        }

        $smPath = public_path('csu-logo-sm.png');
        if (file_exists($smPath)) {
            self::$base64Logo = 'data:image/png;base64,' . base64_encode(file_get_contents($smPath));
            return self::$base64Logo;
        }

        $origPath = public_path('csu-logo.png');
        if (file_exists($origPath)) {
            self::$base64Logo = 'data:image/png;base64,' . base64_encode(file_get_contents($origPath));
            return self::$base64Logo;
        }

        self::$base64Logo = '/csu-logo.png';
        return self::$base64Logo;
    }

    public static function getBrandHtml(string $title = 'PeliCle', string $accentColor = '#0f172a'): string
    {
        $logoSrc = self::getLogoDataUri();

        return '<div class="brand-logo-wrapper" style="display: flex; align-items: center; justify-content: center; gap: 10px; background: transparent !important;">'
            . '<img src="' . $logoSrc . '" alt="CSU Logo" style="height: 2.35rem; width: auto; object-fit: contain; background: transparent !important; flex-shrink: 0;" />'
            . '<span class="brand-title-text font-bold text-xl tracking-wide" style="font-family: \'Outfit\', sans-serif;">' . e($title) . '</span>'
            . '<style>'
            . '.fi-simple-layout .brand-title-text { display: none !important; }'
            . '.fi-simple-layout .fi-logo { height: auto !important; max-height: none !important; display: flex !important; justify-content: center !important; align-items: center !important; }'
            . '.fi-simple-layout .brand-logo-wrapper { justify-content: center !important; gap: 0 !important; width: 100% !important; margin: 0 auto !important; }'
            . '.fi-simple-layout img { height: 4.5rem !important; max-height: 75px !important; width: auto !important; object-fit: contain !important; display: block !important; margin: 0 auto !important; }'
            . 'html.dark .brand-title-text { color: #ffffff !important; }'
            . 'html:not(.dark) .brand-title-text { color: ' . $accentColor . ' !important; }'
            . '.fi-logo, a.fi-logo, .brand-logo-wrapper { background: transparent !important; background-color: transparent !important; box-shadow: none !important; border: none !important; }'
            . '</style>'
            . '</div>';
    }
}
