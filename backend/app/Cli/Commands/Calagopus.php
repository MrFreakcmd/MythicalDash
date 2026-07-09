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
use MythicalDash\Cli\App as CliApp;
use MythicalDash\Cli\CommandBuilder;
use MythicalDash\Config\ConfigFactory;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Chat\Database;
use MythicalDash\Services\PanelManager;
use MythicalDash\Services\Calagopus\Admin\Resources\UserResource;

class Calagopus extends CliApp implements CommandBuilder
{
    public static function getDescription(): string
    {
        return 'Manage Calagopus panel integration and configuration';
    }

    public static function getSubCommands(): array
    {
        return [
            'configure' => 'Configure Calagopus settings',
            'test' => 'Test Calagopus connection',
            'switch' => 'Switch active panel type',
            'status' => 'Show panel configuration status',
            'debug' => 'Show debug information',
        ];
    }

    public static function execute(array $args): void
    {
        $cliApp = CliApp::getInstance();
        $appInstance = App::getInstance(false);

        if (!isset($args[1])) {
            $cliApp->send('&cPlease provide a subcommand!');

            return;
        }

        switch ($args[1]) {
            case 'configure':
                self::configureCalagopus($cliApp, $appInstance);
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

    private static function configureCalagopus(CliApp $cliApp, App $appInstance): void
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

            // Store active_panel_type directly WITHOUT encryption (it's not sensitive data)
            $stmt = $db->getPdo()->prepare("INSERT INTO mythicaldash_settings (name, value, date) VALUES (:name, :value, NOW()) ON DUPLICATE KEY UPDATE value = :value, date = NOW()");
            $stmt->execute(['name' => 'active_panel_type', 'value' => 'calagopus']);

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
                $cliApp->send('&cCalagopus is not configured. Run &ycalagopus configure&c first.');

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

            // Store active_panel_type directly WITHOUT encryption (it's not sensitive data)
            $stmt = $db->getPdo()->prepare("INSERT INTO mythicaldash_settings (name, value, date) VALUES (:name, :value, NOW()) ON DUPLICATE KEY UPDATE value = :value, date = NOW()");
            $result = $stmt->execute(['name' => 'active_panel_type', 'value' => $panelType]);

            if ($result) {
                PanelManager::clearCache();
                $cliApp->send('&a✓ Active panel switched to &f' . strtoupper($panelType) . '&a!');
            } else {
                $cliApp->send('&c✗ Failed to switch panel type!');
            }
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
            $pdo = $db->getPdo();

            // Read active_panel_type directly from database (it's stored as plain text, not encrypted)
            $stmt = $pdo->prepare("SELECT value FROM mythicaldash_settings WHERE name = 'active_panel_type' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $activePanelType = ($result && !empty($result['value'])) ? $result['value'] : 'pterodactyl';

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
                new UserResource($calagopusUrl, $calagopusKey);
                $cliApp->send('&fAPI Connection: &aSuccessful');
            } catch (\Exception $e) {
                $cliApp->send('&fAPI Connection: &c' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $cliApp->send('&c✗ Error: ' . $e->getMessage());
        }
    }
}
