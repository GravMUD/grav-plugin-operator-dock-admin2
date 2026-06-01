<?php

declare(strict_types=1);

namespace Grav\Plugin\OperatorDockAdmin2;

use Grav\Common\Grav;

/**
 * Resolves Operator Dock header links and launch pad tiles.
 */
class OperatorDockLinkRegistry
{
    public function __construct(private readonly Grav $grav) {}

    /** @return array<int, array<string, mixed>> */
    public function headerLinks(): array
    {
        $cfg = (array) $this->grav['config']->get('plugins.grav-operator-dock-admin2', []);
        $links = [];

        if (!empty($cfg['include_view_site'])) {
            $links[] = [
                'label' => 'View Site',
                'url' => $this->siteUrl(),
                'icon' => 'fa-arrow-up-right-from-square',
                'external' => true,
            ];
        }

        if (!empty($cfg['include_grav_learn'])) {
            $links[] = [
                'label' => 'Grav Learn',
                'url' => 'https://learn.getgrav.org',
                'icon' => 'fa-graduation-cap',
                'external' => true,
            ];
        }

        if (!empty($cfg['include_team_dc_pack'])) {
            $links = array_merge($links, self::teamDcPack());
        }

        foreach ($this->customLinks($cfg) as $link) {
            $links[] = $link;
        }

        return $this->uniqueLinks($links);
    }

    /** @return array<string, mixed> */
    public function launchpadPayload(): array
    {
        $cfg = (array) $this->grav['config']->get('plugins.grav-operator-dock-admin2', []);
        $tiles = [];

        foreach ($this->headerLinks() as $link) {
            $tiles[] = [
                'label' => $link['label'],
                'url' => $link['url'],
                'icon' => $link['icon'] ?? 'fa-link',
                'external' => !empty($link['external']),
            ];
        }

        $tiles[] = [
            'label' => 'Pages',
            'url' => $this->adminRoute('/pages'),
            'icon' => 'fa-file-lines',
            'external' => false,
        ];
        $tiles[] = [
            'label' => 'Plugins',
            'url' => $this->adminRoute('/plugins'),
            'icon' => 'fa-puzzle-piece',
            'external' => false,
        ];
        $tiles[] = [
            'label' => 'Themes',
            'url' => $this->adminRoute('/themes'),
            'icon' => 'fa-palette',
            'external' => false,
        ];
        $tiles[] = [
            'label' => 'Tools',
            'url' => $this->adminRoute('/tools'),
            'icon' => 'fa-screwdriver-wrench',
            'external' => false,
        ];
        $tiles[] = [
            'label' => 'Settings',
            'url' => $this->adminRoute('/settings'),
            'icon' => 'fa-sliders',
            'external' => false,
        ];

        $payload = [
            'tiles' => $this->uniqueTiles($tiles),
            'vitals' => !empty($cfg['launchpad_show_vitals']) ? $this->vitals() : null,
        ];

        return $payload;
    }

    /** @return array<string, mixed> */
    private function vitals(): array
    {
        $pages = $this->grav['pages'];
        $theme = (string) $this->grav['config']->get('system.pages.theme', '');

        return [
            'grav_version' => defined('GRAV_VERSION') ? GRAV_VERSION : '',
            'theme' => $theme,
            'page_count' => is_object($pages) && method_exists($pages, 'routes') ? count($pages->routes()) : 0,
            'site_url' => $this->siteUrl(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private static function teamDcPack(): array
    {
        return [
            [
                'label' => 'GetGRAV!',
                'url' => 'https://getgrav.live',
                'icon' => 'fa-rocket',
                'external' => true,
            ],
            [
                'label' => 'Mud Bazaar',
                'url' => 'https://gravmud.site/marketplace',
                'icon' => 'fa-store',
                'external' => true,
            ],
        ];
    }

    /** @param array<string, mixed> $cfg
     * @return array<int, array<string, mixed>>
     */
    private function customLinks(array $cfg): array
    {
        $raw = $cfg['custom_links'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $links = [];
        foreach ($raw as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $url = trim((string) ($entry['url'] ?? ''));
            $label = trim((string) ($entry['label'] ?? ''));
            if ($url === '' || $label === '') {
                continue;
            }
            $links[] = [
                'label' => $label,
                'url' => $url,
                'icon' => trim((string) ($entry['icon'] ?? 'fa-link')) ?: 'fa-link',
                'external' => !empty($entry['external']),
            ];
        }

        return $links;
    }

    /** @param array<int, array<string, mixed>> $links
     * @return array<int, array<string, mixed>>
     */
    private function uniqueLinks(array $links): array
    {
        $seen = [];
        $out = [];
        foreach ($links as $link) {
            $key = strtolower((string) ($link['url'] ?? '')) . '|' . strtolower((string) ($link['label'] ?? ''));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $link;
        }

        return $out;
    }

    /** @param array<int, array<string, mixed>> $tiles
     * @return array<int, array<string, mixed>>
     */
    private function uniqueTiles(array $tiles): array
    {
        return $this->uniqueLinks($tiles);
    }

    private function siteUrl(): string
    {
        /** @var \Grav\Common\Uri $uri */
        $uri = $this->grav['uri'];

        return rtrim($uri->rootUrl(true), '/');
    }

    private function adminRoute(string $suffix): string
    {
        $adminRoute = trim((string) $this->grav['config']->get('plugins.admin2.route', '/admin'), '/');
        /** @var \Grav\Common\Uri $uri */
        $uri = $this->grav['uri'];
        $root = rtrim($uri->rootUrl(false), '/');

        return $root . '/' . $adminRoute . $suffix;
    }
}
