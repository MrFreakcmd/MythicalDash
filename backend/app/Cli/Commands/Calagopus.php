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

namespace MythicalDash\Cli\Commands;

use MythicalDash\App;
use MythicalDash\Cli\CliApp;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\Database;
use MythicalDash\Services\PanelManager;
use MythicalDash\Services\Calagopus\Admin\Resources\UserResource;

class Calagopus extends CliApp implements CommandBuilder
{
    public static function execute(array $args): void
    {
        $cliApp = CliApp::getInstance();
        $appInstance = App::getInstance(true);

        if (!isset($args[1])) {
            $cliApp->send('&cPlease provide a subcommand!');

            return;
        }

        switch ($args[1]) {
            case 'configure':
                self::configureCatagopus($cliApp, $appInstance);
                break;
            case 'test':
                self::testConnection($cliApp, $appInstance);
                break;
            case 'switch':
                self::switchPanel($cliApp, $appInstance);
                break;
            case 'status':
                self::showStatus($cliApp, $appInstance);
                break;
            case 'debug':
                self::debug($cliApp, $appInstance);
                break;
            default:
                $cliApp->send('&cInvalid subcommand!');
                break;
        }
    }

    private static function configureCatagopus(CliApp $cliApp, App $appInstance): void
    {
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD'],
                $_ENV['DATABASE_PORT'],
            );
            $config = new ConfigFactory($db->getPdo());

            $cliApp->send('&e=== Calagopus Configuration ===');
            $cliApp->send('&fEnter Calagopus Base URL (e.g., https://panel.example.com):');
            $baseUrl = trim(fgets(STDIN));

            if (empty($baseUrl)) {
                $cliApp->send('&cBase URL cannot be empty!');

                return;
            }

            $cliApp->send('&fEnter Calagopus API Key:');
            $apiKey = trim(fgets(STDIN));

            if (empty($apiKey)) {
                $cliApp->send('&cAPI Key cannot be empty!');

                return;
            }

            $config->setSetting(ConfigInterface::CALAGOPUS_BASE_URL, $baseUrl);
            $config->setSetting(ConfigInterface::CALAGOPUS_API_KEY, $apiKey);
            $config->setSetting(ConfigInterface::CALAGOPUS_ENABLED, 'true');

            $cliApp->send('&a✓ Calagopus configuration saved!');
        } catch (\Exception $e) {
            $cliApp->send('&c✗ Error: ' . $e->getMessage());
        }
    }

    private static function testConnection(CliApp $cliApp, App $appInstance): void
    {
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD'],
                $_ENV['DATABASE_PORT'],
            );
            $config = new ConfigFactory($db->getPdo());

            $baseUrl = $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, '');
            $apiKey = $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '');

            if (empty($baseUrl) || empty($apiKey)) {
                $cliApp->send('&cCatagopus is not configured. Run &ycalagopus configure&c first.');

                return;
            }

            $cliApp->send('&fTesting Calagopus connection...');

            // Test with UserResource to verify API credentials
            $userResource = new UserResource($baseUrl, $apiKey);
            $users = $userResource->listUsers(1);

            if (!empty($users)) {
                $cliApp->send('&a✓ Connection successful!');
                $cliApp->send('&fFound ' . count($users['data'] ?? []) . ' users in system.');
            } else {
                $cliApp->send('&e⚠ Connection established but received empty response.');
            }
        } catch (\Exception $e) {
            $cliApp->send('&c✗ Connection failed: ' . $e->getMessage());
        }
    }

    private static function switchPanel(CliApp $cliApp, App $appInstance): void
    {
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD'],
                $_ENV['DATABASE_PORT'],
            );
            $config = new ConfigFactory($db->getPdo());

            $cliApp->send('&e=== Switch Panel Type ===');
            $cliApp->send('&f1. Pterodactyl');
            $cliApp->send('&f2. Calagopus');
            $cliApp->send('&fSelect panel (1-2):');
            $choice = trim(fgets(STDIN));

            $panelType = match ($choice) {
                '1' => 'pterodactyl',
                '2' => 'calagopus',
                default => null,
            };

            if ($panelType === null) {
                $cliApp->send('&cInvalid choice!');

                return;
            }

            $config->setSetting(ConfigInterface::ACTIVE_PANEL_TYPE, $panelType);
            PanelManager::clearCache();

            $cliApp->send('&a✓ Active panel switched to &f' . strtoupper($panelType) . '&a!');
        } catch (\Exception $e) {
            $cliApp->send('&c✗ Error: ' . $e->getMessage());
        }
    }

    private static function showStatus(CliApp $cliApp, App $appInstance): void
    {
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD'],
                $_ENV['DATABASE_PORT'],
            );
            $config = new ConfigFactory($db->getPdo());

            $activePanelType = $config->getDBSetting(ConfigInterface::ACTIVE_PANEL_TYPE, 'pterodactyl');

            $cliApp->send('&e=== Panel Configuration Status ===');
            $cliApp->send('&fActive Panel: &a' . strtoupper($activePanelType));

            if ($activePanelType === 'pterodactyl') {
                $pterodactylUrl = $config->getDBSetting(ConfigInterface::PTERODACTYL_BASE_URL, '');
                $pterodactylKey = $config->getDBSetting(ConfigInterface::PTERODACTYL_API_KEY, '');
                $cliApp->send('&fPterodactyl URL: &a' . ($pterodactylUrl ? $pterodactylUrl : '&c(not set)'));
                $cliApp->send('&fPterodactyl API Key: &a' . ($pterodactylKey ? '***' . substr($pterodactylKey, -8) : '&c(not set)'));
            } else {
                $calagopusUrl = $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, '');
                $calagopusKey = $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '');
                $cliApp->send('&fCalagopus URL: &a' . ($calagopusUrl ? $calagopusUrl : '&c(not set)'));
                $cliApp->send('&fCalagopus API Key: &a' . ($calagopusKey ? '***' . substr($calagopusKey, -8) : '&c(not set)'));
            }

            $cliApp->send('&fCalagopus Enabled: &a' . $config->getDBSetting(ConfigInterface::CALAGOPUS_ENABLED, 'false'));
        } catch (\Exception $e) {
            $cliApp->send('&c✗ Error: ' . $e->getMessage());
        }
    }

    private static function debug(CliApp $cliApp, App $appInstance): void
    {
        try {
            $appInstance->loadEnv();
            $db = new Database(
                $_ENV['DATABASE_HOST'],
                $_ENV['DATABASE_DATABASE'],
                $_ENV['DATABASE_USER'],
                $_ENV['DATABASE_PASSWORD'],
                $_ENV['DATABASE_PORT'],
            );
            $config = new ConfigFactory($db->getPdo());

            $cliApp->send('&e=== Calagopus Debug Information ===');

            // Detect active panel
            $activePanelType = PanelManager::getActivePanelType();
            $cliApp->send('&fDetected Panel: &a' . strtoupper($activePanelType));

            // Check configuration
            $calagopusUrl = $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, '');
            $calagopusKey = $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '');

            if (empty($calagopusUrl) || empty($calagopusKey)) {
                $cliApp->send('&e⚠ Calagopus not configured');

                return;
            }

            $cliApp->send('&fCalagopus Configuration: &aPresent');

            // Try connection
            try {
                $userResource = new UserResource($calagopusUrl, $calagopusKey);
                $cliApp->send('&fAPI Connection: &aSuccessful');
            } catch (\Exception $e) {
                $cliApp->send('&fAPI Connection: &c' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $cliApp->send('&c✗ Error: ' . $e->getMessage());
        }
    }
}
