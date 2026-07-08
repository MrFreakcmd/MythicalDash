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
use MythicalDash\Services\Calagopus\Admin\Resources\LocationResource as CalagopusLocationResource;

/**
 * Panel-agnostic Locations Hook - Routes to Pterodactyl or Calagopus based on active panel.
 */
class Locations
{
    /**
     * List all locations from the panel.
     *
     * @param int $page Page number for pagination
     *
     * @return array List of locations
     */
    public static function listAllLocations(int $page = 1): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $admin = new \MythicalDash\Services\Pterodactyl\Admin\Resources\LocationsResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $admin->listLocations($page);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Pterodactyl locations: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusLocationResource) {
                        return $admin->listLocations($page);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Calagopus locations: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to list locations: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get location details by ID.
     *
     * @param string $locationId The location ID
     *
     * @return array Location details
     */
    public static function getLocationDetails(string $locationId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $admin = new \MythicalDash\Services\Pterodactyl\Admin\Resources\LocationsResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $admin->getLocation($locationId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl location: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusLocationResource) {
                        return $admin->getLocation($locationId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus location: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get location details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get location nodes.
     *
     * @param string $locationId The location ID
     *
     * @return array List of nodes in the location
     */
    public static function getLocationNodes(string $locationId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $admin = new \MythicalDash\Services\Pterodactyl\Admin\Resources\LocationsResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $admin->getLocationNodes($locationId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl location nodes: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $admin = PanelManager::getAdminApiInstance();
                    if ($admin instanceof CalagopusLocationResource) {
                        return $admin->getLocationNodes($locationId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus location nodes: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get location nodes: ' . $e->getMessage());

            return [];
        }
    }
}
