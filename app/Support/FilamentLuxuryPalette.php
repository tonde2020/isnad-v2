<?php

declare(strict_types=1);

namespace App\Support;

/**
 * تدرجات ذهبية وبنية محروقة - Filament يحول hex إلى OKLCH عند التسجيل.
 */
final class FilamentLuxuryPalette
{
    /**
     * @return array<int, string>
     */
    public static function primary(): array
    {
        return [
            50 => '#fffdf6',
            100 => '#fcf3df',
            200 => '#f5e4c0',
            300 => '#e9cf92',
            400 => '#dbb55f',
            500 => '#d4af37',
            600 => '#b8922e',
            700 => '#8f7124',
            800 => '#5c4918',
            900 => '#3d2f10',
            950 => '#1f1708',
        ];
    }

    /**
     * رمادي دافئ مائل للبني.
     *
     * @return array<int, string>
     */
    public static function gray(): array
    {
        return [
            50 => '#faf8f5',
            100 => '#f1ebe3',
            200 => '#e0d8cc',
            300 => '#c9bfb0',
            400 => '#a39687',
            500 => '#7d7367',
            600 => '#625a50',
            700 => '#4a433c',
            800 => '#312d28',
            900 => '#1f1c19',
            950 => '#121110',
        ];
    }
}
