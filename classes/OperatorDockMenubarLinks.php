<?php

declare(strict_types=1);

namespace Grav\Plugin\OperatorDockAdmin2;

use Grav\Common\Grav;
use RocketTheme\Toolbox\File\YamlFile;

/**
 * Merges Operator Dock shortcuts into admin-next menubarLinks.
 */
class OperatorDockMenubarLinks
{
    public function __construct(private readonly Grav $grav) {}

    public function mergeConfiguredLinks(): void
    {
        $cfg = (array) $this->grav['config']->get('plugins.grav-operator-dock-admin2', []);
        if (empty($cfg['inject_header_links'])) {
            return;
        }

        $path = $this->adminNextConfigPath(true);
        if (!$path) {
            return;
        }

        $registry = new OperatorDockLinkRegistry($this->grav);
        $toAdd = $registry->headerLinks();
        if ($toAdd === []) {
            return;
        }

        $file = YamlFile::instance($path);
        $data = $file->exists() ? (array) $file->content() : [];
        $ui = is_array($data['ui'] ?? null) ? $data['ui'] : [];
        $settings = is_array($ui['settings'] ?? null) ? $ui['settings'] : [];
        $existing = is_array($settings['menubarLinks'] ?? null) ? $settings['menubarLinks'] : [];

        $merged = $this->mergeUnique($existing, $toAdd);
        if ($merged === $existing) {
            $file->free();
            return;
        }

        $settings['menubarLinks'] = $merged;
        $ui['settings'] = $settings;
        $data['ui'] = $ui;
        $file->save($data);
        $file->free();

        $this->grav['config']->reload();
    }

    /** @param array<int, array<string, mixed>> $existing
     * @param array<int, array<string, mixed>> $toAdd
     * @return array<int, array<string, mixed>>
     */
    private function mergeUnique(array $existing, array $toAdd): array
    {
        $keys = [];
        foreach ($existing as $link) {
            $keys[$this->linkKey($link)] = true;
        }

        $out = $existing;
        foreach ($toAdd as $link) {
            $key = $this->linkKey($link);
            if (isset($keys[$key])) {
                continue;
            }
            $keys[$key] = true;
            $out[] = $link;
        }

        return $out;
    }

    /** @param array<string, mixed> $link */
    private function linkKey(array $link): string
    {
        return strtolower((string) ($link['url'] ?? '')) . '|' . strtolower((string) ($link['label'] ?? ''));
    }

    private function adminNextConfigPath(bool $create): ?string
    {
        $locator = $this->grav['locator'];
        $path = $locator->findResource('user://config/admin-next.yaml', true, true);
        if (!$path || !is_file($path)) {
            $configDir = $locator->findResource('user://config', true, true);
            if (!$configDir && $create) {
                $userPath = $locator->findResource('user://', true, true);
                if (!$userPath) {
                    return null;
                }
                $configDir = $userPath . '/config';
                if (!is_dir($configDir)) {
                    mkdir($configDir, 0775, true);
                }
            }
            if (!$configDir) {
                return null;
            }
            $path = $configDir . '/admin-next.yaml';
        }

        return $path;
    }
}
