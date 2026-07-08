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
use MythicalDash\Services\Calagopus\Client\Resources\AccountResource as CalagopusAccountResource;

/**
 * Panel-agnostic Account Hook (Client) - Routes to Pterodactyl or Calagopus based on active panel.
 */
class Account
{
    /**
     * Get account details.
     *
     * @return array Account details
     */
    public static function getAccountDetails(): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\AccountResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->getAccountDetails();
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl account: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusAccountResource) {
                        return $client->getAccountDetails();
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus account: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get account details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get API keys.
     *
     * @return array List of API keys
     */
    public static function getApiKeys(): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\AccountResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->getApiKeys();
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl API keys: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusAccountResource) {
                        return $client->getApiKeys();
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus API keys: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get API keys: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get SSH keys.
     *
     * @return array List of SSH keys
     */
    public static function getSshKeys(): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\AccountResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->getSshKeys();
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl SSH keys: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusAccountResource) {
                        return $client->getSshKeys();
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus SSH keys: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get SSH keys: ' . $e->getMessage());

            return [];
        }
    }
}
