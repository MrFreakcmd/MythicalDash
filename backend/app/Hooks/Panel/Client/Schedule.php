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
use MythicalDash\Services\Calagopus\Client\Resources\ScheduleResource as CalagopusScheduleResource;

/**
 * Panel-agnostic Schedule Hook (Client) - Routes to Pterodactyl or Calagopus based on active panel.
 */
class Schedule
{
    /**
     * List schedules for a server.
     *
     * @param string $serverId The server ID
     *
     * @return array List of schedules
     */
    public static function listSchedules(string $serverId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ScheduleResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->listSchedules($serverId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Pterodactyl schedules: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusScheduleResource) {
                        return $client->listSchedules($serverId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Calagopus schedules: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to list schedules: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Create a new schedule.
     *
     * @param string $serverId The server ID
     * @param array $data The schedule data
     *
     * @return array Response
     */
    public static function createSchedule(string $serverId, array $data): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ScheduleResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->createSchedule($serverId, $data);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to create Pterodactyl schedule: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusScheduleResource) {
                        return $client->createSchedule($serverId, $data);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to create Calagopus schedule: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to create schedule: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Delete a schedule.
     *
     * @param string $serverId The server ID
     * @param string $scheduleId The schedule ID
     *
     * @return array Response
     */
    public static function deleteSchedule(string $serverId, string $scheduleId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ScheduleResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->deleteSchedule($serverId, $scheduleId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to delete Pterodactyl schedule: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusScheduleResource) {
                        return $client->deleteSchedule($serverId, $scheduleId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to delete Calagopus schedule: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to delete schedule: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Trigger a schedule.
     *
     * @param string $serverId The server ID
     * @param string $scheduleId The schedule ID
     *
     * @return array Response
     */
    public static function triggerSchedule(string $serverId, string $scheduleId): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\ScheduleResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->triggerSchedule($serverId, $scheduleId);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to trigger Pterodactyl schedule: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusScheduleResource) {
                        return $client->triggerSchedule($serverId, $scheduleId);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to trigger Calagopus schedule: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to trigger schedule: ' . $e->getMessage());

            return [];
        }
    }
}
