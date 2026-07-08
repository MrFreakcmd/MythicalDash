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
use MythicalDash\Services\Calagopus\Client\Resources\DatabaseResource as CalagopusDatabaseResource;

/**
 * Panel-agnostic Database Hook (Client) - Routes to Pterodactyl or Calagopus based on active panel.
 */
class Database
{
    /**
     * List databases for a server.
     *
     * @param string $serverId The server ID
     *
     * @return array List of databases
     */
    public static function listDatabases(string $serverId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\DatabaseResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->listDatabases($serverId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Pterodactyl databases: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusDatabaseResource) {
                        return $client->listDatabases($serverId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Calagopus databases: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to list databases: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Create a new database.
     *
     * @param string $serverId The server ID
     * @param string $databaseName The database name
     * @param string $hostname The hostname
     *
     * @return array Response
     */
    public static function createDatabase(string $serverId, string $databaseName, string $hostname): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\DatabaseResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->createDatabase($serverId, $databaseName, $hostname);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to create Pterodactyl database: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusDatabaseResource) {
                        return $client->createDatabase($serverId, $databaseName, $hostname);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to create Calagopus database: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to create database: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Delete a database.
     *
     * @param string $serverId The server ID
     * @param string $databaseId The database ID
     *
     * @return array Response
     */
    public static function deleteDatabase(string $serverId, string $databaseId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\DatabaseResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->deleteDatabase($serverId, $databaseId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to delete Pterodactyl database: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusDatabaseResource) {
                        return $client->deleteDatabase($serverId, $databaseId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to delete Calagopus database: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to delete database: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Rotate database password.
     *
     * @param string $serverId The server ID
     * @param string $databaseId The database ID
     *
     * @return array Response
     */
    public static function rotatePassword(string $serverId, string $databaseId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\DatabaseResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->rotatePassword($serverId, $databaseId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to rotate Pterodactyl database password: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusDatabaseResource) {
                        return $client->rotatePassword($serverId, $databaseId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to rotate Calagopus database password: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to rotate database password: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get database hosts.
     *
     * @param string $serverId The server ID
     *
     * @return array List of database hosts
     */
    public static function getDatabaseHosts(string $serverId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\DatabaseResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->getDatabaseHosts($serverId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl database hosts: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusDatabaseResource) {
                        return $client->getDatabaseHosts($serverId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus database hosts: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get database hosts: ' . $e->getMessage());

            return [];
        }
    }
}
