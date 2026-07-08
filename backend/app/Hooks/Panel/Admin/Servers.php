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

namespace MythicalDash\Hooks\Panel\Admin;

use MythicalDash\App;
use MythicalDash\Services\PanelManager;
use MythicalDash\Services\Pterodactyl\Admin\Resources\ServersResource as PterodactylServersResource;
use MythicalDash\Services\Calagopus\Admin\Resources\ServerResource as CalagopusServerResource;

/**
 * Panel-agnostic Servers Hook - Routes to Pterodactyl or Calagopus based on active panel.
 */
class Servers
{
    /**
     * Get the total resources usage for a user.
     *
     * @param int $panelUserId The ID of the user to get resources for
     * @param bool $forceRefresh Whether to force refresh
     *
     * @return array The total resources usage
     */
    public static function getUserTotalResourcesUsage(int $panelUserId, bool $forceRefresh = false): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                return \MythicalDash\Hooks\Pterodactyl\Admin\Servers::getUserTotalResourcesUsage($panelUserId, $forceRefresh);
            } else {
                // Calagopus implementation would go here
                // For now, fallback to error or implementation
                App::getInstance(true)->getLogger()->info('Calagopus getUserTotalResourcesUsage called for user ' . $panelUserId);

                return [];
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get user total resources usage: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get the list of servers for a user.
     *
     * @param int $panelUserId The ID of the user
     * @param bool $forceRefresh Whether to force refresh
     *
     * @return array The list of servers
     */
    public static function getUserServersList(int $panelUserId, bool $forceRefresh = false): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                return \MythicalDash\Hooks\Pterodactyl\Admin\Servers::getUserServersList($panelUserId, $forceRefresh);
            } else {
                App::getInstance(true)->getLogger()->info('Calagopus getUserServersList called for user ' . $panelUserId);

                return [];
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get user server list: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get server Pterodactyl details by server ID.
     *
     * @param int $serverId The server ID
     *
     * @return array The server details
     */
    public static function getServerPanelDetails(int $serverId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                return \MythicalDash\Hooks\Pterodactyl\Admin\Servers::getServerPterodactylDetails($serverId);
            } else {
                // Calagopus logic
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusServerResource) {
                        return $admin->getServer((string) $serverId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus server details: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get server panel details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * List all servers from the panel.
     *
     * @return array List of servers
     */
    public static function listAllServers(): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $admin = new PterodactylServersResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $admin->listServers();
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Pterodactyl servers: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusServerResource) {
                        return $admin->listServers();
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Calagopus servers: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to list servers: ' . $e->getMessage());

            return [];
        }
    }
}
