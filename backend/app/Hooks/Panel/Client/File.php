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
use MythicalDash\Services\Calagopus\Client\Resources\FileResource as CalagopusFileResource;

/**
 * Panel-agnostic File Hook (Client) - Routes to Pterodactyl or Calagopus based on active panel.
 */
class File
{
    /**
     * List files in server directory.
     *
     * @param string $serverId The server ID
     * @param string $directory The directory path
     *
     * @return array List of files
     */
    public static function listFiles(string $serverId, string $directory = '/'): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\FileResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->listFiles($serverId, $directory);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Pterodactyl files: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusFileResource) {
                        return $client->listFiles($serverId, $directory);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to list Calagopus files: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to list files: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get file contents.
     *
     * @param string $serverId The server ID
     * @param string $filePath The file path
     *
     * @return string File contents
     */
    public static function getFileContents(string $serverId, string $filePath): string
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\FileResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->getFileContents($serverId, $filePath);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Pterodactyl file: ' . $e->getMessage());

                    return '';
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusFileResource) {
                        return $client->getFileContents($serverId, $filePath);
                    }

                    return '';
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to get Calagopus file: ' . $e->getMessage());

                    return '';
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to get file contents: ' . $e->getMessage());

            return '';
        }
    }

    /**
     * Write file contents.
     *
     * @param string $serverId The server ID
     * @param string $filePath The file path
     * @param string $content The file content
     *
     * @return array Response
     */
    public static function writeFile(string $serverId, string $filePath, string $content): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\FileResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->writeFile($serverId, $filePath, $content);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to write Pterodactyl file: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusFileResource) {
                        return $client->writeFile($serverId, $filePath, $content);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to write Calagopus file: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to write file: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Delete files.
     *
     * @param string $serverId The server ID
     * @param array $paths The file paths to delete
     *
     * @return array Response
     */
    public static function deleteFiles(string $serverId, array $paths): array
    {
        try {
            if (PanelManager::isPterodactyl()) {
                try {
                    $client = new \MythicalDash\Services\Pterodactyl\Client\Resources\FileResource(
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_base_url', ''),
                        App::getInstance(true)->getConfig()->getDBSetting('pterodactyl_api_key', '')
                    );

                    return $client->deleteFile($serverId, $paths);
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to delete Pterodactyl files: ' . $e->getMessage());

                    return [];
                }
            } else {
                try {
                    $client = PanelManager::getClientApiInstance();
                    if ($client instanceof CalagopusFileResource) {
                        return $client->deleteFile($serverId, $paths);
                    }

                    return [];
                } catch (\Exception $e) {
                    App::getInstance(true)->getLogger()->error('Failed to delete Calagopus files: ' . $e->getMessage());

                    return [];
                }
            }
        } catch (\Exception $e) {
            App::getInstance(true)->getLogger()->error('Failed to delete files: ' . $e->getMessage());

            return [];
        }
    }
}
