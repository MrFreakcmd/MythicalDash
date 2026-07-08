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
use MythicalDash\Services\Calagopus\Admin\Resources\UserResource as CalagopusUserResource;

/**
 * Panel-agnostic Users Hook - Routes to Pterodactyl or Calagopus based on active panel.
 */
class Users
{
    /**
     * List all users from the panel.
     *
     * @param int $page Page number for pagination
     *
     * @return array List of users
     */
    public static function listAllUsers(int $page = 1): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $admin = new \MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $admin->listUsers($page);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Pterodactyl users: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusUserResource) {
                        return $admin->listUsers($page);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Calagopus users: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to list users: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get user details by ID.
     *
     * @param string $userId The user ID
     *
     * @return array User details
     */
    public static function getUserDetails(string $userId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $admin = new \MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $admin->getUser($userId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl user: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusUserResource) {
                        return $admin->getUser($userId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus user: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get user details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get user servers.
     *
     * @param string $userId The user ID
     *
     * @return array List of user's servers
     */
    public static function getUserServers(string $userId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $admin = new \MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $admin->getUserServers($userId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl user servers: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusUserResource) {
                        return $admin->getUserServers($userId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus user servers: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get user servers: ' . $e->getMessage());

            return [];
        }
    }
}
