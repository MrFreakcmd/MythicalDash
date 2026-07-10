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

/**
 * Panel-agnostic User Hook (Admin) - Routes to Pterodactyl or Calagopus based on active panel.
 */
class User
{
    /**
     * Register a user on the active panel.
     *
     * @param string $firstName User's first name
     * @param string $lastName User's last name
     * @param string $username Username
     * @param string $email Email address
     * @param string $password Password
     *
     * @return int The panel user ID
     *
     * @throws \Exception
     */
    public static function performRegister(string $firstName, string $lastName, string $username, string $email, string $password): int
    {
        if (PanelManager::isPterodactyl()) {
            return \MythicalDash\Hooks\Pterodactyl\Admin\User::performRegister($firstName, $lastName, $username, $email, $password);
        } elseif (PanelManager::isCalagopus()) {
            return \MythicalDash\Hooks\Calagopus\Admin\User::performRegister($firstName, $lastName, $username, $email, $password);
        }

        throw new \Exception('No active panel configured');
    }

    /**
     * Login a user on the active panel.
     *
     * @param int $userId Panel user ID
     * @param string $email Email address
     * @param string $username Username
     * @param string $firstName User's first name
     * @param string $lastName User's last name
     * @param string $password Password
     *
     * @throws \Exception
     */
    public static function performLogin(int $userId, string $email, string $username, string $firstName, string $lastName, string $password): void
    {
        if (PanelManager::isPterodactyl()) {
            \MythicalDash\Hooks\Pterodactyl\Admin\User::performLogin($userId, $email, $username, $firstName, $lastName, $password);
        } elseif (PanelManager::isCalagopus()) {
            \MythicalDash\Hooks\Calagopus\Admin\User::performLogin($userId, $email, $username, $firstName, $lastName, $password);
        } else {
            throw new \Exception('No active panel configured');
        }
    }

    /**
     * Update a user on the active panel.
     *
     * @param int $userId Panel user ID
     * @param string $username Username
     * @param string $email Email address
     * @param string $firstName User's first name
     * @param string $lastName User's last name
     * @param string $password Password
     *
     * @throws \Exception
     */
    public static function performUpdateUser(int $userId, string $username, string $email, string $firstName, string $lastName, string $password): void
    {
        if (PanelManager::isPterodactyl()) {
            $pteroUsers = new \MythicalDash\Services\Pterodactyl\Admin\Resources\UsersResource(
                App::getInstance(false)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                App::getInstance(false)->getConfig()->getDBSetting('pterodactyl_api_key', '')
            );
            \MythicalDash\Hooks\Pterodactyl\Admin\User::performUpdateUser($pteroUsers, $userId, $username, $firstName, $lastName, $email, $password);
        } elseif (PanelManager::isCalagopus()) {
            $calagopusUsers = new \MythicalDash\Services\Calagopus\Admin\Resources\UserResource(
                App::getInstance(false)->getConfig()->getDBSetting('calagopus_base_url', ''),
                App::getInstance(false)->getConfig()->getDBSetting('calagopus_api_key', '')
            );
            \MythicalDash\Hooks\Calagopus\Admin\User::performUpdateUser($calagopusUsers, (string) $userId, $username, $firstName, $lastName, $email, $password);
        } else {
            throw new \Exception('No active panel configured');
        }
    }
}
