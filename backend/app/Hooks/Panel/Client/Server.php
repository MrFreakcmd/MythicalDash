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

namespace MythicalDash\Hooks\Panel\Client;

use MythicalDash\App;
use MythicalDash\Services\PanelManager;
use MythicalDash\Services\Calagopus\Client\Resources\ServerResource as CalagopusServerResource;

/**
 * Panel-agnostic Server Hook (Client) - Routes to Pterodactyl or Calagopus based on active panel.
 */
class Server
{
    /**
     * List servers for the client.
     *
     * @param int $page Page number for pagination
     *
     * @return array List of servers
     */
    public static function listServers(int $page = 1): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ServerResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->listServers();
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Pterodactyl servers: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusServerResource) {
                        return $admin->listServers($page);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Calagopus servers: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to list client servers: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get server details.
     *
     * @param string $serverId The server ID
     *
     * @return array Server details
     */
    public static function getServer(string $serverId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ServerResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->getServer($serverId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl server: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusServerResource) {
                        return $client->getServer($serverId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus server: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get server: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get server resources/utilization.
     *
     * @param string $serverId The server ID
     *
     * @return array Server resources
     */
    public static function getServerResources(string $serverId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ServerResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->getServerUtilization($serverId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl resources: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusServerResource) {
                        return $client->getServerResources($serverId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus resources: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get server resources: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Send power command to server.
     *
     * @param string $serverId The server ID
     * @param string $action The action (start, stop, restart, kill)
     *
     * @return array Response
     */
    public static function sendPowerCommand(string $serverId, string $action): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ServerResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->sendPowerCommand($serverId, $action);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to send Pterodactyl power command: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusServerResource) {
                        return $client->sendPowerCommand($serverId, $action);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to send Calagopus power command: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to send power command: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Send console command to server.
     *
     * @param string $serverId The server ID
     * @param string $command The command to send
     *
     * @return array Response
     */
    public static function sendCommand(string $serverId, string $command): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ServerResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->sendCommand($serverId, $command);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to send Pterodactyl command: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusServerResource) {
                        return $client->sendCommand($serverId, $command);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to send Calagopus command: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to send command: ' . $e->getMessage());

            return [];
        }
    }
}
