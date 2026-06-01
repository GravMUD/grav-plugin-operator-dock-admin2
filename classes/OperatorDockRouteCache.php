<?php

declare(strict_types=1);

namespace Grav\Plugin\OperatorDockAdmin2;

use Grav\Common\Grav;

/**
 * Bust FastRoute cache once per plugin version so new API routes register immediately.
 */
class OperatorDockRouteCache
{
    private const PLUGIN_VERSION = '1.1.0';

    public static function maybeInvalidate(Grav $grav): void
    {
        $dataRoot = $grav['locator']->findResource('user://data', true, true);
        if (!$dataRoot) {
            return;
        }

        $markerDir = $dataRoot . '/operator-dock-admin2';
        if (!is_dir($markerDir)) {
            mkdir($markerDir, 0775, true);
        }

        $marker = $markerDir . '/api-routes.version';
        $stored = is_file($marker) ? trim((string) file_get_contents($marker)) : '';
        if ($stored === self::PLUGIN_VERSION) {
            return;
        }

        $cacheDir = $grav['locator']->findResource('cache://api', true, true);
        if ($cacheDir) {
            $cacheFile = $cacheDir . '/route.cache';
            if (is_file($cacheFile)) {
                @unlink($cacheFile);
            }
        }

        file_put_contents($marker, self::PLUGIN_VERSION);
    }
}
