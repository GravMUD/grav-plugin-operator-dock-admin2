<?php

declare(strict_types=1);

namespace Grav\Plugin\OperatorDockAdmin2;

use Grav\Framework\Psr7\Response;
use Grav\Plugin\Api\Controllers\AbstractApiController;
use Grav\Plugin\Api\Response\ApiResponse;
use Grav\Plugin\Api\Response\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RocketTheme\Toolbox\File\YamlFile;

class OperatorDockApiBridgeController extends AbstractApiController
{
    public function settings(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return new Response(204);
        }

        $this->requirePermission($request, 'api.config.read');

        if ($request->getMethod() === 'GET') {
            return ApiResponse::create($this->readSettings());
        }

        if ($request->getMethod() === 'PATCH') {
            $this->requirePermission($request, 'api.config.write');
            $body = json_decode((string) $request->getBody(), true);
            if (!is_array($body)) {
                return ErrorResponse::create(400, 'Bad Request', 'Invalid JSON body');
            }
            $this->writeSettings($body);

            return ApiResponse::create($this->readSettings());
        }

        return ErrorResponse::create(405, 'Method Not Allowed', 'Use GET or PATCH');
    }

    public function launchpad(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return new Response(204);
        }

        $this->requirePermission($request, 'api.access');

        return ApiResponse::create((new OperatorDockLinkRegistry($this->grav))->launchpadPayload());
    }

    /** @return array<string, mixed> */
    private function readSettings(): array
    {
        $cfg = OperatorDockLegacy::config($this->grav);

        return [
            'enabled' => !empty($cfg['enabled']),
            'inject_header_links' => !empty($cfg['inject_header_links']),
            'include_view_site' => !empty($cfg['include_view_site']),
            'include_grav_learn' => !empty($cfg['include_grav_learn']),
            'include_team_dc_pack' => !empty($cfg['include_team_dc_pack']),
            'show_launchpad_widget' => !empty($cfg['show_launchpad_widget']),
            'launchpad_show_vitals' => !empty($cfg['launchpad_show_vitals']),
            'custom_links' => is_array($cfg['custom_links'] ?? null) ? $cfg['custom_links'] : [],
        ];
    }

    /** @param array<string, mixed> $body */
    private function writeSettings(array $body): void
    {
        $file = YamlFile::instance(OperatorDockLegacy::configFilePath($this->grav));
        $current = $file->exists() ? (array) $file->content() : [];

        foreach ([
            'enabled',
            'inject_header_links',
            'include_view_site',
            'include_grav_learn',
            'include_team_dc_pack',
            'show_launchpad_widget',
            'launchpad_show_vitals',
        ] as $key) {
            if (array_key_exists($key, $body)) {
                $current[$key] = (bool) $body[$key];
            }
        }

        if (array_key_exists('custom_links', $body) && is_array($body['custom_links'])) {
            $current['custom_links'] = $body['custom_links'];
        }

        $file->save($current);
        $file->free();

        $this->config->reload();
    }
}
