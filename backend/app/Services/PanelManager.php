<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Services;

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\Pterodactyl\Client\PterodactylClient;
use MythicalDash\Services\Pterodactyl\Admin\PterodactylAdmin;
use MythicalDash\Services\Calagopus\Client\CalagopusClient;
use MythicalDash\Services\Calagopus\Admin\CalagopusAdmin;

/**
 * Panel Manager - Detects and routes to the appropriate game panel (Pterodactyl or Calagopus).
 */
class PanelManager
{
    /**
     * Get the active panel type (always fresh from database, no caching).
     * This must always return the current value because panel type can change
     * at runtime and registration/API operations depend on accuracy.
     *
     * @return string Either 'pterodactyl' or 'calagopus'
     */
    public static function getActivePanelType(): string
    {
        try {
            $config = App::getInstance(false)->getConfig();
            $panelType = $config->getDBSetting(ConfigInterface::ACTIVE_PANEL_TYPE, 'pterodactyl');
            if (!empty($panelType)) {
                return $panelType;
            }
        } catch (\Exception $e) {
            App::getInstance(false)->getLogger()->error('Failed to detect active panel type: ' . $e->getMessage());
        }
        // Final fallback only if DB read fails
        return 'pterodactyl';
    }

    /**
     * Check if Pterodactyl is the active panel.
     *
     * @return bool
     */
    public static function isPterodactyl(): bool
    {
        return self::getActivePanelType() === 'pterodactyl';
    }

    /**
     * Check if Calagopus is the active panel.
     *
     * @return bool
     */
    public static function isCalagopus(): bool
    {
        return self::getActivePanelType() === 'calagopus';
    }

    /**
     * Get Client API instance for the active panel.
     *
     * @return object Either PterodactylClient or CalagopusClient
     *
     * @throws \Exception
     */
    public static function getClientApiInstance()
    {
        try {
            $config = App::getInstance(false)->getConfig();

            if (self::isCalagopus()) {
                $baseUrl = $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, '');
                $apiKey = $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '');

                if (empty($baseUrl) || empty($apiKey)) {
                    throw new \Exception('Calagopus is not properly configured.');
                }

                return new CalagopusClient($baseUrl, $apiKey);
            } else {
                $baseUrl = $config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, '');
                $apiKey = $config->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '');

                if (empty($baseUrl) || empty($apiKey)) {
                    throw new \Exception('Pterodactyl is not properly configured.');
                }

                return new PterodactylClient($baseUrl, $apiKey);
            }
        } catch (\Exception $e) {
            App::getInstance(false)->getLogger()->error('Failed to get Client API instance: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Get Admin API instance for the active panel.
     *
     * @return object Either PterodactylAdmin or CalagopusAdmin
     *
     * @throws \Exception
     */
    public static function getAdminApiInstance()
    {
        try {
            $config = App::getInstance(false)->getConfig();

            if (self::isCalagopus()) {
                $baseUrl = $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, '');
                $apiKey = $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '');

                if (empty($baseUrl) || empty($apiKey)) {
                    throw new \Exception('Calagopus is not properly configured.');
                }

                return new CalagopusAdmin($baseUrl, $apiKey);
            } else {
                $baseUrl = $config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, '');
                $apiKey = $config->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '');

                if (empty($baseUrl) || empty($apiKey)) {
                    throw new \Exception('Pterodactyl is not properly configured.');
                }

                return new PterodactylAdmin($baseUrl, $apiKey);
            }
        } catch (\Exception $e) {
            App::getInstance(false)->getLogger()->error('Failed to get Admin API instance: ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Set the active panel type (for configuration/testing).
     *
     * @param string $panelType Either 'pterodactyl' or 'calagopus'
     *
     * @throws \InvalidArgumentException
     */
    public static function setActivePanelType(string $panelType): void
    {
        $panelType = strtolower($panelType);

        if (!in_array($panelType, ['pterodactyl', 'calagopus'])) {
            throw new \InvalidArgumentException("Invalid panel type: {$panelType}. Must be 'pterodactyl' or 'calagopus'.");
        }

        try {
            $config = App::getInstance(false)->getConfig();
            $config->setSetting(ConfigInterface::ACTIVE_PANEL_TYPE, $panelType);
        } catch (\Exception $e) {
            App::getInstance(false)->getLogger()->error('Failed to set active panel type: ' . $e->getMessage());
        }
    }

    /**
     * Clear the cached panel type (no-op - panel type is always read fresh from database).
     */
    public static function clearCache(): void
    {
        // No-op: panel type is always read fresh from database, so no cache to clear
    }
}
