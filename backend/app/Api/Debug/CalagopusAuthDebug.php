<?php

/*
 * Calagopus Authentication Debug Endpoint
 *
 * Temporary debugging tool to diagnose Calagopus login issues.
 *
 * Access: GET /api/debug/calagopus-auth
 *
 * DO NOT DEPLOY TO PRODUCTION
 */

namespace MythicalDash\Api\Debug;

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\PanelManager;
use MythicalDash\Services\Calagopus\Auth\CalagopusAuth;

$router->add('/api/debug/calagopus-auth', function (): void {
    $appInstance = App::getInstance(true);
    $config = $appInstance->getConfig();

    // Only allow debug in APP_DEBUG mode
    if (!APP_DEBUG) {
        $appInstance->BadRequest('Debug endpoint disabled', ['error' => 'APP_DEBUG is false']);
        return;
    }

    $debug = [];

    // 1. Check active panel
    $debug['active_panel'] = PanelManager::getActivePanelType();
    $debug['is_calagopus'] = PanelManager::isCalagopus() ? 'YES' : 'NO';
    $debug['is_pterodactyl'] = PanelManager::isPterodactyl() ? 'YES' : 'NO';

    // 2. Check Calagopus configuration
    $calagopusBaseUrl = $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, '');
    $calagopusApiKey = $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '');

    $debug['calagopus_config'] = [
        'base_url_set' => !empty($calagopusBaseUrl) ? 'YES' : 'NO (PROBLEM!)',
        'base_url' => $calagopusBaseUrl ?: '(empty)',
        'api_key_set' => !empty($calagopusApiKey) ? 'YES' : 'NO (PROBLEM!)',
        'api_key_length' => strlen($calagopusApiKey),
    ];

    // 3. Test Calagopus API connectivity
    if (PanelManager::isCalagopus() && !empty($calagopusBaseUrl)) {
        try {
            $calagopusAuth = new CalagopusAuth($calagopusBaseUrl);

            // Try with invalid credentials to test connectivity
            $testResult = $calagopusAuth->authenticate('debug_test_user', 'debug_test_password');
            $debug['calagopus_api_test'] = 'UNEXPECTED_SUCCESS';
        } catch (\MythicalDash\Services\Calagopus\Exceptions\AuthenticationException $e) {
            // Expected - invalid credentials
            $debug['calagopus_api_test'] = [
                'status' => 'CONNECTED',
                'error' => $e->getMessage(),
                'message' => 'API is reachable (got expected auth error)',
            ];
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $debug['calagopus_api_test'] = [
                'status' => 'CONNECTION_FAILED',
                'error' => $e->getMessage(),
                'message' => 'Cannot reach Calagopus API - check URL and network',
            ];
        } catch (\Exception $e) {
            $debug['calagopus_api_test'] = [
                'status' => 'ERROR',
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ];
        }
    } else {
        $debug['calagopus_api_test'] = 'SKIPPED - Not in Calagopus mode or URL not set';
    }

    // 4. Check database
    try {
        $con = \MythicalDash\Chat\Database::getPdoConnection();
        $stmt = $con->prepare('SELECT COUNT(*) as count FROM mythicaldash_config');
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $debug['database'] = [
            'connection' => 'OK',
            'config_rows' => $result['count'] ?? 'unknown',
        ];
    } catch (\Exception $e) {
        $debug['database'] = [
            'connection' => 'FAILED',
            'error' => $e->getMessage(),
        ];
    }

    // 5. Check for Calagopus user ID column in database
    try {
        $con = \MythicalDash\Chat\Database::getPdoConnection();
        $stmt = $con->query('SHOW COLUMNS FROM mythicaldash_users LIKE "calagopus_user_id"');
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $debug['database_schema'] = [
            'calagopus_user_id_column' => $result ? 'EXISTS' : 'MISSING (need migration)',
        ];
    } catch (\Exception $e) {
        $debug['database_schema'] = [
            'error' => $e->getMessage(),
        ];
    }

    $appInstance->OK('Calagopus Debug Info', $debug);
});
