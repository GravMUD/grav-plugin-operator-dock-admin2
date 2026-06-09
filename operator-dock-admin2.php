<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Plugin\OperatorDockAdmin2\OperatorDockApiBridgeController;
use Grav\Plugin\OperatorDockAdmin2\OperatorDockLegacy;
use Grav\Plugin\OperatorDockAdmin2\OperatorDockRouteCache;
use Grav\Plugin\OperatorDockAdmin2\TeamDcMenubarCoordinator;
use RocketTheme\Toolbox\Event\Event;

class OperatorDockAdmin2Plugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        $events = [
            'onPluginsInitialized' => [['onPluginsInitializedEarly', 100000]],
        ];

        if (self::supportsGravApiBridge()) {
            $events['onApiRegisterRoutes'] = ['onApiRegisterRoutes', 0];
            $events['onApiAdminSettingsPanels'] = ['onApiAdminSettingsPanels', 0];
            $events['onApiSidebarItems'] = ['onApiSidebarItems', 0];
            $events['onApiPluginPageInfo'] = ['onApiPluginPageInfo', 0];
            $events['onApiMenubarItems'] = ['onApiMenubarItems', 0];
            $events['onApiMenubarAction'] = ['onApiMenubarAction', 0];
            $events['onApiAdminPreferencesResolved'] = ['onApiAdminPreferencesResolved', 0];
            $events['onApiDashboardWidgets'] = ['onApiDashboardWidgets', 0];
        }

        return $events;
    }

    public function onPluginsInitializedEarly(): void
    {
        if (!self::supportsGravApiBridge()) {
            return;
        }

        require_once __DIR__ . '/classes/OperatorDockLegacy.php';
        if (!$this->isEnabled()) {
            return;
        }

        $this->loadClasses();
        OperatorDockLegacy::maybeMigrate($this->grav);
        OperatorDockRouteCache::maybeInvalidate($this->grav);
    }

    public function onApiRegisterRoutes(Event $event): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->loadClasses();

        $routes = $event['routes'];
        $controller = OperatorDockApiBridgeController::class;

        $routes->addRoute(['GET', 'PATCH', 'OPTIONS'], '/operator-dock/settings', [$controller, 'settings']);
        $routes->addRoute(['GET', 'OPTIONS'], '/operator-dock/launchpad', [$controller, 'launchpad']);
    }

    public function onApiAdminSettingsPanels(Event $event): void
    {
        if (!$this->isEnabled() || !$this->canUseSettings($event['user'] ?? null)) {
            return;
        }

        $panels = $event['panels'] ?? [];
        $panels[] = [
            'id' => 'operator-dock-admin2',
            'plugin' => 'operator-dock-admin2',
            'label' => 'Operator Dock',
            'description' => 'Header shortcuts, launch pad links, and quick ops',
            'icon' => 'fa-compass',
            'blueprint' => 'operator-dock-settings',
            'data_endpoint' => '/config/plugins/operator-dock-admin2',
            'save_endpoint' => '/config/plugins/operator-dock-admin2',
            'priority' => 12,
        ];
        $event['panels'] = $panels;
    }

    public function onApiSidebarItems(Event $event): void
    {
        if (!$this->isEnabled() || !is_dir(GRAV_ROOT . '/user/plugins/operator-dock-admin2')) {
            return;
        }

        if (!$this->canUseAdmin($event['user'] ?? null)) {
            return;
        }

        $items = $event['items'] ?? [];
        $items[] = [
            'id' => 'operator-dock-admin2',
            'plugin' => 'operator-dock-admin2',
            'label' => 'Operator Dock',
            'icon' => 'fa-compass',
            'route' => '/plugin/operator-dock-admin2',
            'priority' => 84,
        ];
        $event['items'] = $items;
    }

    public function onApiPluginPageInfo(Event $event): void
    {
        $plugin = (string) ($event['plugin'] ?? '');
        if (!$this->isEnabled() || !in_array($plugin, [OperatorDockLegacy::SLUG, OperatorDockLegacy::LEGACY_SLUG], true)) {
            return;
        }

        if (!$this->canUseAdmin($event['user'] ?? null)) {
            return;
        }

        $event['definition'] = [
            'id' => 'operator-dock-admin2',
            'plugin' => 'operator-dock-admin2',
            'title' => 'Operator Dock',
            'icon' => 'fa-compass',
            'page_type' => 'blueprint',
            'blueprint' => 'operator-dock-admin2',
            'data_endpoint' => '/config/plugins/operator-dock-admin2',
            'save_endpoint' => '/config/plugins/operator-dock-admin2',
            'actions' => [
                ['id' => 'save', 'label' => 'Save', 'icon' => 'fa-check', 'primary' => true],
            ],
        ];
    }

    public function onApiAdminPreferencesResolved(Event $event): void
    {
        if (!$this->isEnabled() || !$this->canUseAdmin($event['user'] ?? null)) {
            return;
        }

        $this->loadClasses();
        $payload = $event['payload'] ?? [];
        if (!is_array($payload)) {
            return;
        }

        $event['payload'] = TeamDcMenubarCoordinator::mergeIntoPayload($payload, $this->grav);
    }

    public function onApiMenubarItems(Event $event): void
    {
        if (!$this->isEnabled() || !$this->canUseAdmin($event['user'] ?? null)) {
            return;
        }

        $items = $event['items'] ?? [];

        $cfg = OperatorDockLegacy::config($this->grav);
        if (!empty($cfg['show_clear_cache_button'])) {
            $items[] = [
                'id' => 'operator-dock-clear-cache',
                'plugin' => 'operator-dock-admin2',
                'label' => 'Clear cache',
                'icon' => 'fa-broom',
                'action' => 'clear-cache',
                'confirm' => 'Clear standard cache now?',
                'authorize' => 'api.system.write',
                'priority' => 20,
            ];
        }

        $event['items'] = $items;
    }

    public function onApiMenubarAction(Event $event): void
    {
        $plugin = (string) ($event['plugin'] ?? '');
        if (!in_array($plugin, [OperatorDockLegacy::SLUG, OperatorDockLegacy::LEGACY_SLUG], true)) {
            return;
        }

        if (($event['action'] ?? '') !== 'clear-cache') {
            return;
        }

        $user = $event['user'] ?? null;
        if (!$user || !($user->get('access.api.super') || $user->get('access.api.system.write'))) {
            $event['result'] = [
                'status' => 'error',
                'message' => 'Insufficient permissions to clear cache.',
            ];
            return;
        }

        try {
            $this->grav['cache']->clearCache('standard');
            $event['result'] = [
                'status' => 'success',
                'message' => 'Standard cache cleared.',
            ];
        } catch (\Throwable $e) {
            $event['result'] = [
                'status' => 'error',
                'message' => 'Cache clear failed: ' . $e->getMessage(),
            ];
        }
    }

    public function onApiDashboardWidgets(Event $event): void
    {
        if (!$this->isEnabled() || !$this->canUseAdmin($event['user'] ?? null)) {
            return;
        }

        $cfg = OperatorDockLegacy::config($this->grav);
        if (empty($cfg['show_launchpad_widget'])) {
            return;
        }

        $widgets = $event['widgets'] ?? [];
        $widgets[] = [
            'id' => 'operator-dock.launchpad',
            'plugin' => 'operator-dock-admin2',
            'label' => 'Operator Launch Pad',
            'icon' => 'Rocket',
            'sizes' => ['sm', 'md', 'lg', 'xl'],
            'defaultSize' => 'md',
            'authorize' => 'api.access',
            'priority' => 72,
            'scriptUrl' => $this->widgetScriptUrl(),
            'dataEndpoint' => '/operator-dock/launchpad',
        ];
        $event['widgets'] = $widgets;
    }

    private function widgetScriptUrl(): string
    {
        return '/gpm/plugins/operator-dock-admin2/widget-script';
    }

    private function isEnabled(): bool
    {
        return OperatorDockLegacy::isEnabled($this->grav);
    }

    /** @param mixed $user */
    private function canUseAdmin($user): bool
    {
        if (!$user || !is_object($user) || !method_exists($user, 'get')) {
            return false;
        }

        return (bool) ($user->get('access.api.access') || $user->get('access.api.super'));
    }

    /** @param mixed $user */
    private function canUseSettings($user): bool
    {
        if (!$user || !is_object($user) || !method_exists($user, 'get')) {
            return false;
        }

        if ($user->get('access.api.super')) {
            return true;
        }

        return (bool) ($user->get('access.api.config.read') || $user->get('access.api.config.write'));
    }

    private function loadClasses(): void
    {
        require_once __DIR__ . '/classes/OperatorDockLegacy.php';
        require_once __DIR__ . '/classes/OperatorDockLinkRegistry.php';
        require_once __DIR__ . '/classes/OperatorDockMenubarLinks.php';
        require_once __DIR__ . '/classes/TeamDcMenubarCoordinator.php';
        require_once __DIR__ . '/classes/OperatorDockRouteCache.php';
        require_once __DIR__ . '/classes/OperatorDockApiBridgeController.php';
    }

    private static function supportsGravApiBridge(): bool
    {
        return class_exists(\Grav\Plugin\Api\ApiRouteCollector::class);
    }
}
