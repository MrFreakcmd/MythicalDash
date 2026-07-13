<?php

/*
 * Calagopus API Debug & Test Script
 *
 * Tests the actual API call to Calagopus with verbose output
 * Shows request/response details for debugging
 */

require 'vendor/autoload.php';

use MythicalDash\App;
use MythicalDash\Config\ConfigInterface;
use MythicalDash\Services\PanelManager;
use MythicalDash\Services\Calagopus\Auth\CalagopusAuth;

echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
echo "CALAGOPUS API DEBUG TEST" . PHP_EOL;
echo "═════════════════════════════════════════════════════════════" . PHP_EOL . PHP_EOL;

$appInstance = App::getInstance(true);
$config = $appInstance->getConfig();

// 1. Get configuration from database
echo "1. DATABASE CONFIGURATION" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

$activePanel = $config->getDBSetting(ConfigInterface::ACTIVE_PANEL_TYPE, 'pterodactyl');
$baseUrl = $config->getDBSetting(ConfigInterface::CALAGOPUS_BASE_URL, '');
$apiKey = $config->getDBSetting(ConfigInterface::CALAGOPUS_API_KEY, '');

echo "Active Panel Type: " . $activePanel . PHP_EOL;
echo "Calagopus Base URL: " . ($baseUrl ?: '(NOT SET)') . PHP_EOL;
echo "Calagopus API Key: " . (
    $apiKey ?
    substr($apiKey, 0, 10) . "..." . substr($apiKey, -10) . " (length: " . strlen($apiKey) . ")" :
    "(NOT SET)"
) . PHP_EOL;

if (!PanelManager::isCalagopus()) {
    echo PHP_EOL . "⚠️  WARNING: Active panel is NOT Calagopus! Current: $activePanel" . PHP_EOL;
    echo "   This means loginPterodactyl() will be called instead of loginCalagopus()" . PHP_EOL;
}

if (empty($baseUrl)) {
    echo PHP_EOL . "⚠️  WARNING: Calagopus base URL is not set!" . PHP_EOL;
    echo "   loginCalagopus() will return 'false' early" . PHP_EOL;
}

if (empty($apiKey)) {
    echo PHP_EOL . "⚠️  WARNING: Calagopus API key is not set!" . PHP_EOL;
}

// 2. Check .env file
echo PHP_EOL . PHP_EOL;
echo "2. ENVIRONMENT FILE (.env)" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $envLines = array_filter(explode("\n", $envContent), fn($line) =>
        strpos($line, 'ACTIVE_PANEL') !== false ||
        strpos($line, 'CALAGOPUS') !== false ||
        strpos($line, 'APP_DEBUG') !== false
    );

    foreach ($envLines as $line) {
        if (strpos($line, 'API_KEY') !== false) {
            // Mask API key for security
            echo preg_replace('/=[^=]*$/', '=(masked)', $line) . PHP_EOL;
        } else {
            echo $line . PHP_EOL;
        }
    }
} else {
    echo ".env file not found" . PHP_EOL;
}

// 3. Test Calagopus API connectivity
echo PHP_EOL . PHP_EOL;
echo "3. CALAGOPUS API TEST" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

if (!$baseUrl || !$apiKey) {
    echo "⚠️  Skipping API test - Base URL or API Key not configured" . PHP_EOL;
} else {
    echo "Testing connection to: " . $baseUrl . PHP_EOL;
    echo "Using API Key: " . substr($apiKey, 0, 15) . "..." . PHP_EOL . PHP_EOL;

    try {
        $calagopusAuth = new CalagopusAuth($baseUrl);

        echo "Attempting test authentication with dummy credentials..." . PHP_EOL;
        echo "Request: POST /api/auth/login" . PHP_EOL;
        echo "Payload: {\"username\": \"test_user\", \"password\": \"test_password\"}" . PHP_EOL . PHP_EOL;

        try {
            $result = $calagopusAuth->authenticate('test_user', 'test_password');
            echo "✅ Response: SUCCESS (unexpected - got user data)" . PHP_EOL;
            echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
        } catch (\MythicalDash\Services\Calagopus\Exceptions\AuthenticationException $e) {
            // Expected - test credentials should be invalid
            echo "✅ Response: 401 Unauthorized (Expected - test credentials are invalid)" . PHP_EOL;
            echo "   Error: " . $e->getMessage() . PHP_EOL;
            echo PHP_EOL . "   ✅ This means the API IS reachable and responding correctly!" . PHP_EOL;
        }
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
        echo "❌ Connection Failed: Cannot reach Calagopus API" . PHP_EOL;
        echo "   Error: " . $e->getMessage() . PHP_EOL;
        echo PHP_EOL . "   Troubleshooting:" . PHP_EOL;
        echo "   - Check if Calagopus is running and accessible" . PHP_EOL;
        echo "   - Verify the base URL: " . $baseUrl . PHP_EOL;
        echo "   - Check network connectivity and firewall rules" . PHP_EOL;
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . PHP_EOL;
        echo "   Type: " . get_class($e) . PHP_EOL;
    }
}

// 4. Check database schema
echo PHP_EOL . PHP_EOL;
echo "4. DATABASE SCHEMA CHECK" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

try {
    $con = \MythicalDash\Chat\Database::getPdoConnection();

    // Check calagopus_user_id column
    $stmt = $con->query("SHOW COLUMNS FROM mythicaldash_users LIKE 'calagopus_user_id'");
    $column = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($column) {
        echo "✅ calagopus_user_id column EXISTS" . PHP_EOL;
        echo "   Type: " . $column['Type'] . PHP_EOL;
    } else {
        echo "❌ calagopus_user_id column MISSING (Migration not applied)" . PHP_EOL;
    }

    // Count users
    $stmt = $con->query("SELECT COUNT(*) as count FROM mythicaldash_users");
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    echo "   Total users in database: " . $result['count'] . PHP_EOL;

} catch (\Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . PHP_EOL;
}

// 5. Simulate login attempt
echo PHP_EOL . PHP_EOL;
echo "5. LOGIN FLOW SIMULATION" . PHP_EOL;
echo str_repeat("─", 60) . PHP_EOL;

echo "If you were to login with username='testuser' password='testpass':" . PHP_EOL;
echo "Expected flow:" . PHP_EOL;

if (!PanelManager::isCalagopus()) {
    echo "  1. ❌ Active panel is NOT Calagopus" . PHP_EOL;
    echo "  2. ❌ User::login() will call loginPterodactyl() instead" . PHP_EOL;
    echo "  3. ❌ loginPterodactyl() will check local DB password" . PHP_EOL;
    echo "  4. ❌ Since user is Calagopus-only, pterodactyl_user_id=0 → login fails" . PHP_EOL;
    echo "  5. ❌ Return 'false' → 'Invalid credentials'" . PHP_EOL;
} else if (empty($baseUrl)) {
    echo "  1. ✅ Active panel IS Calagopus" . PHP_EOL;
    echo "  2. ✅ User::login() will call loginCalagopus()" . PHP_EOL;
    echo "  3. ❌ loginCalagopus() checks for base URL" . PHP_EOL;
    echo "  4. ❌ Base URL is empty!" . PHP_EOL;
    echo "  5. ❌ Return 'false' early" . PHP_EOL;
} else if (empty($apiKey)) {
    echo "  1. ✅ Active panel IS Calagopus" . PHP_EOL;
    echo "  2. ✅ User::login() will call loginCalagopus()" . PHP_EOL;
    echo "  3. ✅ loginCalagopus() checks for base URL - OK" . PHP_EOL;
    echo "  4. ❌ CalagopusAuth created, but API key is empty!" . PHP_EOL;
    echo "  5. ❌ API call will fail or authenticate as anonymous" . PHP_EOL;
} else {
    echo "  1. ✅ Active panel IS Calagopus" . PHP_EOL;
    echo "  2. ✅ User::login() will call loginCalagopus()" . PHP_EOL;
    echo "  3. ✅ Base URL configured" . PHP_EOL;
    echo "  4. ✅ API key configured" . PHP_EOL;
    echo "  5. ✅ CalagopusAuth will attempt API call..." . PHP_EOL;
    echo "  6. → Will call POST " . $baseUrl . "/api/auth/login" . PHP_EOL;
    echo "  7. → With Authorization header and credentials" . PHP_EOL;
}

echo PHP_EOL;
echo "═════════════════════════════════════════════════════════════" . PHP_EOL;
