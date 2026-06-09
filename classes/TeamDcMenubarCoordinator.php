<?php

declare(strict_types=1);

namespace Grav\Plugin\OperatorDockAdmin2;

use Grav\Common\Grav;

/**
 * Merges runtime Team DC menubar URL links into admin-next preferences.
 *
 * Admin2 renders URL shortcuts from effective.menubarLinks (anchor tags).
 * onApiMenubarItems is action-only — url fields there POST undefined actions.
 */
class TeamDcMenubarCoordinator
{
    /** @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function mergeIntoPayload(array $payload, Grav $grav): array
    {
        $runtime = self::collect($grav);
        if ($runtime === []) {
            return $payload;
        }

        $effective = is_array($payload['effective'] ?? null) ? $payload['effective'] : [];
        $existing = is_array($effective['menubarLinks'] ?? null) ? $effective['menubarLinks'] : [];
        $effective['menubarLinks'] = self::uniqueLinks(array_merge($existing, $runtime));
        $payload['effective'] = $effective;

        return $payload;
    }

    /** @return list<array<string, mixed>> */
    public static function collect(Grav $grav): array
    {
        $links = [];

        if (self::javaBeanInjecting($grav)) {
            foreach (self::javaBeanPreferenceLinks($grav) as $link) {
                $links[] = $link;
            }
        }

        if (self::operatorDockInjecting($grav)) {
            foreach ((new OperatorDockLinkRegistry($grav))->headerLinks() as $link) {
                $links[] = $link;
            }
        }

        if (self::mamboDesktopInjecting($grav)) {
            $links[] = self::mamboDesktopLink($grav);
        }

        return self::uniqueLinks($links);
    }

    /** @return list<array<string, mixed>> */
    private static function javaBeanPreferenceLinks(Grav $grav): array
    {
        if (!class_exists(\Grav\Plugin\JavaBeanAdmin2\JavaBeanMenubarLinks::class)) {
            $path = GRAV_ROOT . '/user/plugins/javabean-admin2/classes/JavaBeanMenubarLinks.php';
            if (is_file($path)) {
                require_once $path;
            }
        }
        if (!class_exists(\Grav\Plugin\JavaBeanAdmin2\JavaBeanMenubarLinks::class)) {
            return [];
        }

        return \Grav\Plugin\JavaBeanAdmin2\JavaBeanMenubarLinks::preferenceLinks($grav);
    }

    private static function mamboDesktopLink(Grav $grav): array
    {
        return [
            'label' => 'Mambo Desktop',
            'url' => self::adminPluginRoute($grav, 'mambo-desktop-admin2'),
            'icon' => 'fa-desktop',
            'external' => false,
        ];
    }

    private static function adminPluginRoute(Grav $grav, string $pluginSlug): string
    {
        $adminRoute = trim((string) $grav['config']->get('plugins.admin2.route', '/admin'), '/');
        /** @var \Grav\Common\Uri $uri */
        $uri = $grav['uri'];
        $root = rtrim($uri->rootUrl(false), '/');

        return $root . '/' . $adminRoute . '/plugin/' . $pluginSlug;
    }

    private static function javaBeanInjecting(Grav $grav): bool
    {
        if (!class_exists(\Grav\Plugin\JavaBeanAdmin2\JavaBeanLegacy::class)) {
            $path = GRAV_ROOT . '/user/plugins/javabean-admin2/classes/JavaBeanLegacy.php';
            if (is_file($path)) {
                require_once $path;
            }
        }
        if (!class_exists(\Grav\Plugin\JavaBeanAdmin2\JavaBeanLegacy::class)) {
            return false;
        }

        return \Grav\Plugin\JavaBeanAdmin2\JavaBeanLegacy::isEnabled($grav)
            && !empty(\Grav\Plugin\JavaBeanAdmin2\JavaBeanLegacy::config($grav)['inject_menubar_links']);
    }

    private static function operatorDockInjecting(Grav $grav): bool
    {
        if (!OperatorDockLegacy::isEnabled($grav)) {
            return false;
        }

        return !empty(OperatorDockLegacy::config($grav)['inject_header_links']);
    }

    private static function mamboDesktopInjecting(Grav $grav): bool
    {
        $cfg = (array) $grav['config']->get('plugins.mambo-desktop-admin2', []);

        return !empty($cfg['enabled']) && !empty($cfg['show_menubar_shortcut']);
    }

    /**
     * @param array<int, array<string, mixed>> $links
     * @return list<array<string, mixed>>
     */
    public static function uniqueLinks(array $links): array
    {
        $seen = [];
        $out = [];
        foreach ($links as $link) {
            if (!is_array($link)) {
                continue;
            }
            $url = trim((string) ($link['url'] ?? ''));
            $label = trim((string) ($link['label'] ?? ''));
            if ($url === '' || $label === '') {
                continue;
            }
            $key = strtolower($url) . '|' . strtolower($label);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = [
                'label' => $label,
                'url' => $url,
                'icon' => trim((string) ($link['icon'] ?? 'fa-link')) ?: 'fa-link',
                'external' => !empty($link['external']),
            ];
        }

        return $out;
    }
}
