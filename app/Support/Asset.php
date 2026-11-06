<?php

namespace App\Support;

/**
 * asset() with a filemtime query string, so a changed stylesheet actually
 * invalidates in the browser and an unchanged one is cached for a year.
 */
final class Asset
{
    public static function url(string $path): string
    {
        $file = public_path(ltrim($path, '/'));
        $version = is_file($file) ? filemtime($file) : null;

        return asset($path).($version ? '?v='.$version : '');
    }
}
