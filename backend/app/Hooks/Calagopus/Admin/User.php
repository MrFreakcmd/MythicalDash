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

namespace MythicalDash\Hooks\Calagopus\Admin;

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\Calagopus\Admin\Resources\UserResource;
use MythicalDash\Services\Calagopus\Exceptions\ResourceNotFoundException;

class User
{
    /**
     * Perform a login action on the Calagopus panel.
     *
     * @todo Implement Calagopus panel sync when panel API is fully ready
     * For now, this is stubbed to prevent incomplete API calls from blocking login.
     *
     * @param int $calagopusUserId The ID of the user to login
     * @param string $email The email of the user to login
     * @param string $username The username of the user to login
     * @param string $firstName The first name of the user to login
     * @param string $lastName The last name of the user to login
     * @param string $password The password of the user to login
     *
     * @throws \Exception
     */
    public static function performLogin(int $calagopusUserId, string $email, string $username, string $firstName, string $lastName, string $password): void
    {
        $appInstance = App::getInstance(false);
        $appInstance->getLogger()->debug('[Calagopus/Admin/User#performLogin] Calagopus panel sync not yet implemented - skipping');
        // TODO: Implement full Calagopus user sync when ready
    }

    /**
     * Perform a register action on the Calagopus panel.
     *
     * @todo Implement Calagopus panel sync when panel API is fully ready
     * For now, this is stubbed to prevent incomplete API calls from blocking registration.
     *
     * @param string $firstName The first name of the user to register
     * @param string $lastName The last name of the user to register
     * @param string $username The username of the user to register
     * @param string $email The email of the user to register
     * @param string $password The password of the user to register
     *
     * @return int A placeholder user ID
     *
     * @throws \Exception
     */
    public static function performRegister(string $firstName, string $lastName, string $username, string $email, string $password): int
    {
        $appInstance = App::getInstance(true);
        $appInstance->getLogger()->debug('[Calagopus/Admin/User#performRegister] Calagopus panel sync not yet implemented - skipping');
        // TODO: Implement full Calagopus user creation when ready
        // Return a placeholder ID for now
        return 0;
    }

    /**
     * Perform an update user action on the Calagopus panel.
     *
     * @todo Implement Calagopus panel sync when panel API is fully ready
     * For now, this is stubbed to prevent incomplete API calls from blocking updates.
     *
     * @param UserResource $userResource The user resource instance
     * @param string $userId The ID of the user to update
     * @param string $username The username of the user to update
     * @param string $firstName The first name of the user to update
     * @param string $lastName The last name of the user to update
     * @param string $email The email of the user to update
     * @param string $password The password of the user to update
     *
     * @throws \Exception
     */
    public static function performUpdateUser(UserResource $userResource, string $userId, string $username, string $firstName, string $lastName, string $email, string $password): void
    {
        $appInstance = App::getInstance(true);
        $appInstance->getLogger()->debug('[Calagopus/Admin/User#performUpdateUser] Calagopus panel sync not yet implemented - skipping');
        // TODO: Implement full Calagopus user update when ready
    }
}
