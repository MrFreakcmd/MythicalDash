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
        $config = $appInstance->getConfig();

        try {
            $userResource = new UserResource(
                $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, ''),
                $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '')
            );

            // Get the user to ensure they exist
            $user = $userResource->getUser((string) $calagopusUserId);
            if (empty($user)) {
                $appInstance->getLogger()->error('[Calagopus/Admin/User#performLogin:1] User data is empty: ' . $calagopusUserId);
                throw new \Exception('User data is empty: ' . $calagopusUserId);
            }

            // Update user with current login details
            self::performUpdateUser($userResource, (string) $calagopusUserId, $username, $firstName, $lastName, $email, $password);
        } catch (ResourceNotFoundException $e) {
            // User not found
            $appInstance->getLogger()->error('[Calagopus/Admin/User#performLogin:2] User not found by id: ' . $calagopusUserId);
            throw new \Exception('User not found by id: ' . $calagopusUserId);
        } catch (\Exception $e) {
            $appInstance->getLogger()->error('[Calagopus/Admin/User#performLogin:3] Failed to update user in Calagopus: ' . $e->getMessage());
            throw new \Exception('Failed to update user in Calagopus: ' . $e->getMessage());
        }
    }

    /**
     * Perform a register action on the Calagopus panel.
     *
     * @param string $firstName The first name of the user to register
     * @param string $lastName The last name of the user to register
     * @param string $username The username of the user to register
     * @param string $email The email of the user to register
     * @param string $password The password of the user to register
     *
     * @return int The user id of the user in the Calagopus panel
     *
     * @throws \Exception
     */
    public static function performRegister(string $firstName, string $lastName, string $username, string $email, string $password): int
    {
        $appInstance = App::getInstance(true);
        $config = $appInstance->getConfig();

        try {
            $userResource = new UserResource(
                $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, ''),
                $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '')
            );

            // Check if user exists by email first
            try {
                $user = $userResource->getUser($email);
                if (!empty($user) && isset($user['attributes']['id'])) {
                    $appInstance->getLogger()->info('[Calagopus/Admin/User#performRegister] User already exists by email: ' . $email);
                    return (int) $user['attributes']['id'];
                }
            } catch (\Exception $e) {
                // User not found by email, continue
            }

            // Check if user exists by username
            try {
                // Note: Calagopus API may not have findByUsername, so we list and search
                $users = $userResource->listUsers();
                if (!empty($users['data'])) {
                    foreach ($users['data'] as $existingUser) {
                        if (($existingUser['attributes']['username'] ?? null) === $username) {
                            $appInstance->getLogger()->info('[Calagopus/Admin/User#performRegister] User already exists by username: ' . $username);
                            return (int) $existingUser['attributes']['id'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue with creation if search fails
            }

            // If we get here, the user doesn't exist, so create them
            $newUser = $userResource->createUser([
                'email' => $email,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => $password,
            ]);

            if (empty($newUser)) {
                throw new \Exception('Failed to register user in Calagopus: Empty response');
            }

            $userId = $newUser['attributes']['id'] ?? null;
            if (!$userId) {
                throw new \Exception('Failed to extract user ID from Calagopus response');
            }

            $appInstance->getLogger()->info('[Calagopus/Admin/User#performRegister] New user created in Calagopus with ID: ' . $userId);
            return (int) $userId;
        } catch (\Exception $e) {
            $appInstance->getLogger()->error('[Calagopus/Admin/User#performRegister] Failed to register user in Calagopus: ' . $e->getMessage());
            throw new \Exception('Failed to register user in Calagopus: ' . $e->getMessage());
        }
    }

    /**
     * Perform an update user action on the Calagopus panel.
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

        try {
            $userResource->updateUser($userId, [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        } catch (\Exception $e) {
            $appInstance->getLogger()->error('[Calagopus/Admin/User#performUpdateUser] Failed to update user in Calagopus: ' . $e->getMessage());
            throw new \Exception('Failed to update user in Calagopus: ' . $e->getMessage());
        }
    }
}

