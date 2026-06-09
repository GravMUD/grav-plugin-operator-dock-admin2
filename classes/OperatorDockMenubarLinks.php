<?php

declare(strict_types=1);

namespace Grav\Plugin\OperatorDockAdmin2;

use Grav\Common\Grav;

/**
 * Legacy menubar hook — URL shortcuts now merge via TeamDcMenubarCoordinator.
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
        return [];
    }
}
