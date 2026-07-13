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

use MythicalDash\App;
use MythicalDash\Config\PublicConfig;

$router->add('/api/system/settings', function (): void {
    App::init();
    $appInstance = App::getInstance(false);
    try {
        $config = $appInstance->getConfig();

        $settingsPublic = PublicConfig::getPublicSettingsWithDefaults();

        // Retrieve actual settings with defaults in a single array map
        try {
            $settings = $config->getSettings(array_keys($settingsPublic));
        } catch (\Exception $e) {
            // If database query fails (empty table, migration issues, etc), use defaults
            $appInstance->getLogger()->warning('Failed to fetch settings from database: ' . $e->getMessage() . '. Using defaults.');
            $settings = [];
        }

        // Fill in any missing settings with defaults
        foreach ($settingsPublic as $key => $defaultValue) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultValue;
            }
        }

        App::OK('Sure here are the settings you were looking for', ['settings' => $settings, 'core' => []]);
    } catch (\Exception $e) {
        // Final fallback: return defaults if anything goes wrong
        $appInstance->getLogger()->error('Settings endpoint error: ' . $e->getMessage());

        $settingsPublic = PublicConfig::getPublicSettingsWithDefaults();
        App::OK('Sure here are the settings you were looking for', ['settings' => $settingsPublic, 'core' => []]);
    }
});
