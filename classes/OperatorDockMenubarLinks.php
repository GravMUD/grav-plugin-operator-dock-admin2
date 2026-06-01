<?php

declare(strict_types=1);

namespace Grav\Plugin\OperatorDockAdmin2;

use Grav\Common\Grav;

/**
 * Runtime Admin2 menubar shortcuts via onApiMenubarItems (no admin-next.yaml writes).
 */
class OperatorDockMenubarLinks
{
    public function __construct(private readonly Grav $grav)
    {
    }

    public function shouldInject(): bool
    {
        $cfg = OperatorDockLegacy::config($this->grav);

        return !empty($cfg['enabled']) && !empty($cfg['inject_header_links']);
    }

    /** @return list<array<string, mixed>> */
    public function apiItems(): array
    {
        if (!$this->shouldInject()) {
            return [];
        }

        $items = [];
        foreach ((new OperatorDockLinkRegistry($this->grav))->headerLinks() as $index => $link) {
            $url = trim((string) ($link['url'] ?? ''));
            $label = trim((string) ($link['label'] ?? ''));
            if ($url === '' || $label === '') {
                continue;
            }

            $items[] = [
                'id' => 'operator-dock-link-' . substr(md5(strtolower($url) . '|' . strtolower($label)), 0, 12),
                'plugin' => 'operator-dock-admin2',
                'label' => $label,
                'icon' => trim((string) ($link['icon'] ?? 'fa-link')) ?: 'fa-link',
                'url' => $url,
                'external' => !empty($link['external']),
                'priority' => 30 + $index,
            ];
        }

        return $items;
    }
}
